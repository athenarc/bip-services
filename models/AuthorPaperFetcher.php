<?php

/* 
 * Model that looks for particular authors' papers
 */

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\sphinx\Query;
use yii\data\Pagination;

/**
 * Model with functions to return synonym authors and their respective papers
 */
class AuthorPaperFetcher extends Model
{
    public $author_kwd;
    public $synonym_author_list;
    public $ordering;
    public $full_name_given = false;
    /*
     * 
     */
    public function __construct($author, $ordering = '')
    {   
        $this->author_kwd = $author; 
        $this->ordering = $this->get_ordering($ordering);
        //Get synonyms
        $this->synonym_author_list = $this->find_synonyms($author);
        //Format author names
        $this->synonym_author_list = array_map(array($this, 'format_author_name'), $this->synonym_author_list);
        
        //If we have a single synonym, format the name
        if(count($this->synonym_author_list) == 1)
        {
            $this->author_kwd = $this->format_author_name($this->synonym_author_list[0]);
        }
        else if(count(explode(" ", $this->author_kwd)) > 1)
        {
            $this->author_kwd = $this->format_author_name($this->author_kwd);
            $this->full_name_given = true;
        }
    }
    
    private function get_ordering($ordering_string)
    {
        if ($ordering_string == 'popularity' || $ordering_string == 'influence')
        {
            return $ordering_string;
        }
        return 'popularity';
    }
    /**
      * Get the authorfetcher elements labels.
      *
      * @return Array containing the model's elements labels in case it's in a form.
      *
      * @author Ilias Kanellos
      */
    public function attributeLabels()
    {
	return ['ordering' => 'Order by:'];
    }
    
    /*
     * Get literals for many author names
     * 
     * @author Ilias Kanellos
     */
    private function get_author_literals($author_list_string, $author)
    {
        $author_list = explode(",", $author_list_string);
        $author_literals = array();
        //Split every author into two parts - either one could be the given name
        foreach($author_list as $single_author)
        {
            if((stripos(preg_replace("/\s+/", " ", $single_author), preg_replace("/\s+/", " ", $author)) !== FALSE) )
            {
                array_push($author_literals, $single_author);
            }
        }
        //Return the array after removing possible duplicates
        return array_unique($author_literals);
       
    }
    
    /*
     * Hlias IMPORTANT NOTE: In order to override the name of the form elements,
     * we need to set a custom returned formName function. The default behaviour
     * when we call activeForm is to set the model name as the name of an array
     * and the input element names as the hash keys of that array. E.g. here
     * we would have elements called "AuthorPaperFetcher['<input_element>']". To keep
     * only the name of the input element, we return an empty string
     * 
     * @return empty string as form identifier.
     *
     * @author Ilias Kanellos
     */
    public function formName()
    {
        return '';
    }    
    
    
    private function find_synonyms($author)
    {
        //Query all papers that have the author keyword
        
        //We do not get all results just now, we do this after setting the max limit parameter.
        //To do that, we need to issue the query and get from the metadata the actual number of 
        //results. In order to issue a "showMeta" we need to use the ->search() method, instead
        //of the ->all() method.
        $authors_sphinx_query = (new \yii\sphinx\Query())
            ->select(['authors'])
            ->from('bcn_authors')
            ->match(new Expression(':match', ['match' => '@(authors) "' . Yii::$app->sphinx->escapeMatchValue($author) . '"']))
            ->showMeta(true)
            ->search();     
        
        //Get the actual number of results
        $num_documents = (array_key_exists("docs[0]", $authors_sphinx_query["meta"])) ? $authors_sphinx_query["meta"]["docs[0]"]  : 1;
        //In case results are zero, set correct limit
        $num_documents = ($num_documents == 0) ? 1 : $num_documents;

        //Now query all the documents to get all possible distinct author names
        $authors_sphinx_query_full = (new \yii\sphinx\Query())
            ->select(['authors'])
            ->from('bcn_authors')
            ->match(new Expression(':match', ['match' => '@(authors) "' . Yii::$app->sphinx->escapeMatchValue($author) . '"']))
            ->options(['max_matches' => (int) $num_documents])
            ->offset(0)
            ->limit($num_documents)
            ->all();   
        
        
        //Initialize array of authors
        $this->synonym_author_list = array();
        //Get the author literal(s) from every author list found
        foreach($authors_sphinx_query_full as $author_row)
        {
            $author_literals = $this->get_author_literals($author_row["authors"], $this->author_kwd);
            foreach($author_literals as $single_author)
            {
                $single_author = $this->format_author_name(strtolower($single_author));
                $this->synonym_author_list[$single_author] = 1;
            }
        }
        
        return array_keys($this->synonym_author_list);
    }
    
    /*
     * Get the actual ordering method (column name)
     * 
     * @return the actual db column name to order results by.
     *
     * @author Ilias Kanellos
     */       
    private function getRankingMethod($method)
    {
        if ($method == "popularity")
        {
            return 'futurerank1';
        }
        else if ($method == "year")
        {
            return 'year';
        }
        else if ($method == "influence")
        {
            return 'pagerank';
        }
        else
        {
            return 'futurerank1';
        }
    }
        
    /*
     * This method should run only when there is a particular author name requested
     * which cannot be associated with more than one strings. E.g. we have
     * "smith e", not "smith". The latter should lead us to a disambiguation page
     */
    public function get_author_papers()
    {
        //Get possible user 
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);
        
        //Get number of author's papers - Query the author-specific index
        $count_query = (new \yii\sphinx\Query())
            ->select(['total' => 'count(*)'])
            ->from('bcn_authors')
            ->match(new Expression(':match', ['match' => '@(authors) "' . Yii::$app->sphinx->escapeMatchValue($this->author_kwd) . '"']))
            ->all();
        //Get number of results
        $total_count = $count_query ? $count_query[0]['total'] : 1;
        $total_count = ($total_count == 0) ? 1: $total_count;
        //Create the pagination object. - The papers examined should be only the valid ones
        $pagination = new Pagination(['totalCount'=>$total_count]);
  
        //Get author's papers
        $author_papers_query = (new \yii\sphinx\Query())
            ->select(['id'])
            ->from('bcn_authors')
            ->match(new Expression(':match', ['match' => '@(authors) "' . Yii::$app->sphinx->escapeMatchValue($this->author_kwd) . '"']))
            ->orderBy($this->getRankingMethod($this->ordering)  . ' DESC')
            ->options(['max_matches' => (int)$total_count])
            ->offset($pagination->offset)
            ->limit($pagination->limit);

        $author_papers_sphinx = $author_papers_query->all();
        //Get the actual paper ids - these will be queried in the database
        $author_paper_ids = array_column($author_papers_sphinx, 'id');
               
        $author_papers = array();
        if(!empty($author_paper_ids))
        {
            //Author papers from database
            $author_papers = (new \yii\db\Query())
                    ->select(['internal_id', 'pmc', 'title','journal','year', 'user_id'])
                    ->from('pmc_paper')
                    ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
                    ->where(['in', 'internal_id', $author_paper_ids])
                    ->orderBy([new \yii\db\Expression('FIELD (internal_id, ' . implode(',',$author_paper_ids) . ')')])
                    ->all();
        }

        //Return results
        return ['author_papers'=>$author_papers, 'pagination'=>$pagination/*, 'query'=>$query*/, 'count'=>$total_count]; 
    }
    
    /*
     * Format author name
     * 
     * @returns String
     * 
     * @author Ilias Kanellos
     */
    public function format_author_name($author_name)
    {
        $author_name_parts = preg_split("/\s+/", $author_name);
        //Loop all names - uppercase their first letter
        foreach($author_name_parts as $key=>$name_part)
        {
            //If name consists of multiple hyphenated names, uppercase each part of them
            $name_part_parts = preg_split("/-/", $name_part);
            foreach($name_part_parts as $inner_key=>$part)
            {
                $name_part_parts[$inner_key] = ucfirst($part);
            }
            $name_part = implode("-", $name_part_parts);
            //If current name part has 2 letters or less, uppercase all of them (probably a middle name)
            if(strlen($name_part) <= 2)
            {
                $name_part = strtoupper($name_part);
            }
            $author_name_parts[$key] = $name_part;
        }
        return \implode(" ", $author_name_parts);
    }
    
    /*
     * Get statistics for each author synonym from author-specific index
     */
    public function get_synonym_author_stats($synonym_list)
    {
        $synonym_stats = array();
        //Loop each group
        foreach($synonym_list as $synonym)
        {
            $synonym_stats[$synonym] = $this->get_exact_match_stats($synonym);
        }
        return $synonym_stats;
    }
    
    /*
     * Get the top venue and start year for particular, single, author name (exact)
     *
     * @return associative array with journals and starting year
     * 
     * @author Ilias Kanellos
     */
    private function get_exact_match_stats($author_exact_name)
    {
        //Get number of author's papers
        $count_query = (new \yii\sphinx\Query())
            ->select(['total' => 'count(*)'])
            ->from('bcn_authors')
            ->match(new Expression(':match', ['match' => '@(authors) "' . Yii::$app->sphinx->escapeMatchValue($author_exact_name) . '"']))
            ->all();
        $total_count = $count_query ? $count_query[0]['total'] : 1;
        $total_count = ($total_count == 0) ? 1 : $total_count;
        
        
        $years_active_array = [3000,-1];
        $journals = array();
        //Query all results 
        $results = (new \yii\sphinx\Query())
                ->select(['id','journal','year', 'authors'])
                ->from('bcn_authors')
                ->match(new Expression(':match', ['match' => '@(authors) "' . Yii::$app->sphinx->escapeMatchValue($author_exact_name) . '"']))
                ->options(['max_matches' => (int)$total_count])
                ->orderBy('pagerank DESC')
                ->offset(0)
                ->limit((int)$total_count)
                ->all();
            
        
        foreach($results as $result)
        {
            //Current record's journal & year
            $journal = $result['journal'];
            $year = $result['year'];
            //Add journal to journals array
            $journals[$journal] = 1;   
          
            //Add start-end year
            if($year != 0)
            {
                $years_active_array[0] = ($year < $years_active_array[0]) ? $year : $years_active_array[0];
                $years_active_array[1] = ($year > $years_active_array[1]) ? $year : $years_active_array[1];
            }
            
        }
        //Fix journals in array - currently they are in associative array form
        $journals = array_keys($journals);
        
        return ['journals' => $journals, 'active_periods' => $years_active_array];
    }
    
}