<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\Url;
use app\models\Article;
use Wamania\Snowball\English;

/**
 * The model behind the search form.
 *
 * @author Thanasis Vergoulis
 */
class SearchForm extends Model
{
    public $keywords;
    public $ordering;
    public $location;
    public $relevance;
    public $topics;
    public $start_year;
    public $end_year;
    public $author_kwd;
    public $influence;
    public $popularity;
    public $impulse;
    public $cc;
    public $type;
    public $space_model;

    /*
         * Constructor method to help with validation
         * Also set attributes
         *
         * @return empty string as form identifier.
     *
     * @author Ilias Kanellos
     */
        public function __construct($ordering, $keywords, $location, $relevance = null, $topics = [], $start_year = 0, $end_year=0, $influence = 'all', $popularity = 'all', $impulse = 'all', $cc = 'all', $type = [], $space_model = null)
        {
          parent::__construct();
          $this->ordering = $ordering;
          $this->keywords = $keywords;
          $this->relevance = $relevance;
          $this->start_year = intval($start_year);
          $this->end_year = intval($end_year);

          if(!empty($topics))
          {
            $this->topics = array_filter($topics);
          }
          else
          {
              $this->topics = [];
          }
          $location_parts = explode("-", $location);
          /*
           * Set the selected search to initially empty
           */
          $this->location = [];
          //If abstract/title were specified, insert them
          foreach ($location_parts as $location)
          {
              if ($location != '')
              {
                  array_push($this->location, $location);
              }
          }

          $this->author_kwd = array();
          $this->influence = $influence;
          $this->popularity = $popularity;
          $this->impulse = $impulse;
          $this->cc = $cc;
          $this->type = $type;
          $this->space_model = $space_model;
        }

        /*
         * Hlias IMPORTANT NOTE: In order to override the name of the form elements,
         * we need to set a custom returned formName function. The default behaviour
         * when we call activeForm is to set the model name as the name of an array
         * and the input element names as the hash keys of that array. E.g. here
         * we would have elements called "SearchForm['<input_element>']". To keep
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

        /*
         * Get the actual ordering method (column name)
         *
         * @return the actual db column name to order results by.
     *
     * @author Ilias Kanellos
     */
        private function getRankingMethod($method)
        {
            return Yii::$app->params['impact_fields'][$method];
        }


    /**
     * Get the validation rules.
     *
     * @return Array containing the validation rules.
     *
     * @author Thanasis Vergoulis
     */
    public function rules()
    {
        return [
           //Set the parameter ordering to be null on empty string
           ['ordering', 'default', 'value' => 'popularity'],
           //If both checkboxes were empty, set them both as selected
           ['location', 'default', 'value' => ['title', 'abstract']],
           ['relevance', 'default', 'value' => 'high'],

           // filters default values
           ['influence', 'default', 'value' => 'all'],
           ['popularity', 'default', 'value' => 'all'],
           ['impulse', 'default', 'value' => 'all'],
           ['cc', 'default', 'value' => 'all'],

           ['start_year', 'compare', 'compareValue' =>'end_year', 'operator' => '<=', 'type' => 'number']

       ];
    }

    /**
     * Get the form elements labels.
     *
     * @return Array containing the form elements labels.
     *
     * @author Thanasis Vergoulis
     */
    public function attributeLabels()
    {
        return [
            'keywords'=>'',
            'ordering' => 'Ordering:',
            'location' => 'Search in:',
            'cc' => 'Citation Count',
        ];
    }

        /*
         * Turn the author keywords into suitable urls for the author pages
         *
         * @return list of html strings
         *
         * @author Ilias Kanellos
         */
        public function process_author_urls()
        {
            $authors_processed = array();
            foreach($this->author_kwd as $author)
            {
                if (strlen($author) >= 2)
                {
                    $author = trim($author);
                    array_push($authors_processed, "<a href='" . Url::to(['site/author', 'author' => $author]) . "'>$author</a>");
                }
            }
            return $authors_processed;
        }


        /*
         * Returns whether string starts with punctiation mark
         *
         * @return boolean
         *
         * @author Ilias Kanellos
         */
        private function starts_with_punctuation_mark($string)
        {
            if(substr($string, 0, 1 ) == "." || substr($string, 0, 1 ) == "!" ||
               substr($string, 0, 1 ) == "?" || substr($string, 0, 1 ) == ":" ||
               substr($string, 0, 1 ) == ";")
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /*
         * Removes first word if it starts with a punctuation mark
         *
         * @return string
         *
         * @author Ilias Kanellos
         */
        private function remove_first_word($string)
        {
            $string = explode(" ", $string);
            array_shift($string);
            $string = implode(" ", $string);
            $string = "[...] " . $string;
            return $string;
        }

        /*
         * Returns an array of phrases containing the particular keyword searched
         *
         * @return array of strings
         *
         * @author Ilias Kanellos
         */
        private function get_kwd_matches($article_data, $keyword, $lowercase_it=true, $add_brackets=true)
        {
            if($lowercase_it)
            {
                $kwd_to_search = strtolower($keyword);
            }
            else
            {
               $kwd_to_search = $keyword;
            }

            //ATTENTION: HERE WE ADDED POSSIBLE END OF STRING CHARACTER TO END OF MATCH.
            //CHECK IF THIS LEAVES THINGS WORKING AS INTENDED. IF CONTEXT DOESNT SEEM RIGHT,
            //REMOVE THE FINAL $ FROM THE REGULAR EXPRESSION (also the parentheses and the logical "or")
            preg_match_all("/(^|[\.\?!:;])[^\.\?!:;]*?". preg_quote($kwd_to_search, "/") . "[^\.\?!:;]*?([\.\?!:;]|$)/i", $article_data, $kwd_matches);
            $context_array = array();
            //Add a sentence each time to context array
            if (!empty($kwd_matches[0]))
            {
                foreach($kwd_matches[0] as $match)
                {
                    if (substr($match, 1, 1 ) === " ")
                    {
                        $match = substr($match, 2);
                    }
                    if($this->starts_with_punctuation_mark($match))
                    {
                        $match = $this->remove_first_word($match);
                    }
                    $match = trim($match);
                    if ($add_brackets)
                    {
                        $match = preg_replace('/[\.\?!:;]$/', '', $match) . " [...]";
                    }
                    $match = htmlspecialchars($match);
                    array_push($context_array, $match);
                }
            }
            return $context_array;
        }

        private static function split_and_stem_keywords($keywords) {
            $stemmer = new English();

            $kwds = preg_split("/\s+/", preg_replace('/[\'"]+/', '', trim($keywords)));
            $stemmed_keywords = [];

            foreach ($kwds as $kwd) {
                $stem = $stemmer->stem($kwd);
                if ($stem != $kwd) {
                    array_push($stemmed_keywords, $stem);
                }
            }

            return array_merge($kwds, $stemmed_keywords);
        }

        private static function assignClass($paper, $score, $scores_levels, $impact_type) {

            // papers with no scores (eg datasets)
            if( !isset($paper[$score]) )
                $class = null;
            elseif( $paper[$score] >= $scores_levels[$impact_type . "_top001"])
                $class = "A";
            elseif( $paper[$score] >= $scores_levels[$impact_type . "_top01"])
                $class = "B";
            elseif( $paper[$score] >= $scores_levels[$impact_type . "_top1"])
                $class = "C";
            elseif( $paper[$score] >= $scores_levels[$impact_type . "_top10"])
                $class = "D";
            else
                $class = "E";

            return $class;
        }


        /**
         * Adds the impact class for each entry in $rows.
         *
         * @param $rows The entries (each entry is a paper).
         * @return Returns $rows with included the impact class of each row.
         *
         * @author Thanasis Vergoulis
         */
        public static function get_impact_class($rows)
        {
            $res = (new \yii\db\Query())->select("*")->from('low_category_scores_view')->one();

            foreach($rows as $key => $row) {

                $rows[$key]['pop_class'] = SearchForm::assignClass($rows[$key], 'attrank', $res, 'popularity');
                $rows[$key]['inf_class'] = SearchForm::assignClass($rows[$key], 'pagerank', $res, 'influence');
                $rows[$key]['imp_class'] = SearchForm::assignClass($rows[$key], '3y_cc', $res, 'impulse');
                $rows[$key]['cc_class'] = SearchForm::assignClass($rows[$key], 'citation_count', $res, 'cc');

            }
            return $rows;
        }

        public static function get_concepts_impact_class($rows)
        {
            $res = (new \yii\db\Query())->select("*")->from('concepts_low_category_scores_view')->all();
            $res = \yii\helpers\ArrayHelper::index($res, 'concept_id');

            foreach($rows as $key => $row) {

                foreach ($row['concepts'] as $concept_data) {

                $concept_data['pop_class'] = SearchForm::assignClass($rows[$key], 'attrank', $res[$concept_data['id']], 'popularity');
                $concept_data['inf_class'] = SearchForm::assignClass($rows[$key], 'pagerank', $res[$concept_data['id']], 'influence');
                $concept_data['imp_class'] = SearchForm::assignClass($rows[$key], '3y_cc', $res[$concept_data['id']], 'impulse');
                $concept_data['cc_class'] = SearchForm::assignClass($rows[$key], 'citation_count', $res[$concept_data['id']], 'cc');

                // append the element to array
                $concepts_with_impact[] = $concept_data;

                }
                // if a paper has concepts
                if (isset($concepts_with_impact)) {
                    $rows[$key]['concepts'] = $concepts_with_impact;
                    unset($concepts_with_impact);
                }

            }
            return $rows;
        }

        public static function transformPercentageToClass($percentage)
        {
            if ($percentage <= 0.01/100){
                $class = "A";
            } elseif ($percentage <= 0.1/100) {
                $class = "B";
            } elseif ($percentage <= 1/100) {
                $class = "C";
            } elseif ($percentage <= 10/100) {
                $class = "D";
            } else
                $class = "E";

            return $class;
        }

        /**
         * ???
         */
        private function get_context($rows) {
            $keywords = SearchForm::split_and_stem_keywords($this->keywords);

            foreach($rows as $key => $row)
            {
                $rows[$key]["search_context"] = [];

                //Get context in abstract
                $article_contexts_abstract = $this->get_kwd_context($row['internal_id'], "abstract", $keywords);
                $article_contexts_abstract = $this->enclose_kwds_in_span($article_contexts_abstract, true, $keywords);
                $rows[$key]['search_context']["abstract"] = $article_contexts_abstract;

                //Get context in title
                $article_contexts_title = $this->get_kwd_context($row['internal_id'], "title", $keywords);
                $article_contexts_title = $this->enclose_kwds_in_span($article_contexts_title, true, $keywords);
                $rows[$key]['search_context']['title'] = $article_contexts_title;

                //Get kwd in author field!
                $contexts_authors = $this->get_author_context($row['internal_id'], $keywords);
                $contexts_authors = $this->enclose_kwds_in_span($contexts_authors, false, $keywords);
                $rows[$key]['search_context']['author'] = $contexts_authors;
            }
            return $rows;
        }

        /**
         * Get the context of found keywords.
         *
         * @return an array containing the context of the keywords found in a paper
         *
         * @author Ilias Kanellos
         */
        private function get_kwd_context($paper_id, $field="abstract", $kwd_array)
        {
            if ($field != "abstract" && $field != "title")
            {
                $field = "abstract";
            }
            //Get the article object and return it's text.
            $article_data = Article::find()->select($field)->where(['internal_id' => $paper_id])->one();
            $article_data = $article_data[$field];

            //Context array contains sentenctes with the context
            $context_array = array();

            //Get the context for each kwd
            foreach($kwd_array as $keyword)
            {
                //Get all matches, add them to context_array
                $contexts = $this->get_kwd_matches($article_data, $keyword);
                $context_array = array_merge($context_array, $contexts);
            }
            //Remove duplicates from context
            $context_array = array_unique($context_array);

            //Return contexts
            return $context_array;
        }

        private function get_author_context($paper_id, $kwd_array)
        {
            //Get the article object and return it's text.
            $article_authors = Article::find()->select('authors')->where(['internal_id' => $paper_id])->one();
            $article_authors = $article_authors['authors'];
            //$author_list = str_split($article_authors, ",");

            $context_array = array();

            foreach($kwd_array as $keyword)
            {
                //Get all matches, add them to context_array
                $keyword = ucfirst($keyword);
                $contexts = $this->get_kwd_matches($article_authors, $keyword, false, false);
                $context_array = array_merge($context_array, $contexts);
                if(!empty($context_array))
                {
                    if(!in_array($keyword, $this->author_kwd))
                    {
                        array_push($this->author_kwd, $keyword);
                    }
                }
            }
            //Remove duplicates from context
            $context_array = array_unique($context_array);
            //Return contexts
            return $context_array;
        }

        private function enclose_kwds_in_span($context_array, $lc_first=true, $keywords)
        {
            if (!$lc_first)
            {
                $keywords = array_map('ucfirst', $keywords);
            }
            foreach($context_array as $offset => $context)
            {
                foreach($keywords as $kwd)
                {
                    if($lc_first)
                    {
                        $kwd = lcfirst($kwd);
                    }
                    $context_array[$offset] = str_ireplace($kwd, "<span class='highlight-kwd'>$kwd</span>", $context_array[$offset]);
                }
            }
            return $context_array;
        }


    public function getImpactScoreFilters() {

        $last_influence_score = 0;
        $last_popularity_score = 0;
        $last_impulse_score = 0;
        $last_cc_score = 0;

        // if influence filter is set, find the appropriate min influence score to be used in the following queries
        if ($this->influence != 'all') {
            // find the lowest influence score in that category
            $field = "influence_" . $this->influence;
            $res = (new \yii\db\Query())->select($field)->from('low_category_scores_view')->one();
            $last_influence_score = $res[$field];
        }

        if ($this->popularity != 'all') {
            // find the lowest popularity score in that category
            $field = "popularity_" . $this->popularity;
            $res = (new \yii\db\Query())->select($field)->from('low_category_scores_view')->one();
            $last_popularity_score = $res[$field];
        }

        if ($this->impulse != 'all') {
            // find the lowest impulse score in that category
            $field = "impulse_" . $this->impulse;
            $res = (new \yii\db\Query())->select($field)->from('low_category_scores_view')->one();
            $last_impulse_score = $res[$field];
        }

        if ($this->cc != 'all') {
            // find the lowest impulse score in that category
            $field = "cc_" . $this->cc;
            $res = (new \yii\db\Query())->select($field)->from('low_category_scores_view')->one();
            $last_cc_score = $res[$field];
        }

        return [
            'influence' => $last_influence_score,
            'popularity' => $last_popularity_score,
            'impulse' => $last_impulse_score,
            'cc' => $last_cc_score,
        ];
    }

    public function prepareSearchQuery() {

        // create a Solr select query
        $query = Yii::$app->solr->createSelect();

        // use dismax query parser and query specific fields
        $dismax = $query->getDisMax();
        $dismax->setQueryFields('title abstract authors doi');

        // return only paper ids and score
        $query->setFields(['internal_id', 'influence', 'popularity', 'impulse', 'citation_count', 'score']);

        // change to 'AND' as default query operator
        $query->setQueryDefaultOperator('AND');

        // set Solr query 'q' parameterer after escaping keywords
        $query->setQuery($this->keywords);

        // add appropriate filters
        if ($this->start_year != 0) {
            $query->createFilterQuery('star_year_filter')->setQuery('year:[' . $this->start_year . ' TO *]');
        }

        if ($this->end_year != 0) {
            $query->createFilterQuery('end_year_filter')->setQuery('year:[* TO ' . $this->end_year .']');
        }

        if ($this->topics) {
            $topics_filter = [];

            // escape topic names and prepend 'topic' field
            foreach ($this->topics as $key => $topic_name) {
                array_push($topics_filter, "concepts:" . $query->getHelper()->escapePhrase($topic_name));
            }

            // join them with 'OR' opearator
            $topics_filter = implode(" OR ", $topics_filter);
            $query->createFilterQuery('topics_filter')->setQuery($topics_filter);
        }

        if ($this->type) {
            $type_filter = [];

            // escape type names and prepend 'type' field
            foreach ($this->type as $key => $type_value) {
                array_push($type_filter, "type:" . $query->getHelper()->escapePhrase($type_value));
            }

            // join them with 'OR' opearator
            $type_filter = implode(" OR ", $type_filter);
            $query->createFilterQuery('type_filter')->setQuery($type_filter);
            
        }

        // get min impact scores to be added to query (set by impact category filter)
        $min_impact_scores = $this->getImpactScoreFilters();

        if ($this->influence != 'all') {
            $query->createFilterQuery('influence_filter')->setQuery('influence:[' . $min_impact_scores['influence'] . ' TO *]');
        }

        if ($this->popularity != 'all') {
            $query->createFilterQuery('popularity_filter')->setQuery('popularity:[' . $min_impact_scores['popularity'] . ' TO *]');
        }

        if ($this->impulse != 'all') {
            $query->createFilterQuery('impulse_filter')->setQuery('impulse:[' . $min_impact_scores['impulse'] . ' TO *]');
        }

        if ($this->cc != 'all') {
            $query->createFilterQuery('cc_filter')->setQuery('citation_count:[' . $min_impact_scores['cc'] . ' TO *]');
        }

        // do not consider keyword relevance when:
        // * relevance is set to 'low'
        // * ordering is set to 'year'
        if ($this->relevance == "low" || $this->ordering == "year") {
            
            $query->addSort($this->ordering, $query::SORT_DESC);

        // keyword relevance specified, sort by min-max relevance & impact scores
        } else {
            $max_scores_query = clone $query;

            // sort based on chosen impact
            $max_scores_query->addSort($this->ordering, $max_scores_query::SORT_DESC);

            // Set the number of results to return
            $max_scores_query->setRows(1);

            // Set the 0-based result to start from
            $max_scores_query->setStart(0);
            $result = Yii::$app->solr->select($max_scores_query);
            $response = $result->getData()['response'];

            // get max relevance & impact scores
            $max_relevance_score = $result->getMaxScore();

            $max_relevance_score = ($max_relevance_score) ? $max_relevance_score : 1;

            // no articles found
            if (count($response['docs']) == 0) {
                return null;
            }

            $max_impact_score = $response['docs'][0][$this->ordering];
            $max_impact_score = ($max_impact_score) ? $max_impact_score : 1;

            // form sort clauses based on min-max relavance & impact scores
            $min_sort_clause = 'min(sqrt(sqrt(div(' . $this->ordering . ',' . $max_impact_score . '))),' .
                            'div(query({!dismax v=$q}),' . $max_relevance_score .'))';
            $query->addSort($min_sort_clause, $query::SORT_DESC);

            $max_sort_clause = 'max(sqrt(sqrt(div(' . $this->ordering . ',' . $max_impact_score . '))),' .
                            'div(query({!dismax v=$q}),' . $max_relevance_score .'))';
            $query->addSort($max_sort_clause, $query::SORT_DESC);

        }

        return $query;
    }

    private function performSearchQuery($query) {

        // create the pagination object
        // if we do not pass 'totalCount' in the constructor, 'limit' & 'offset' below are always for page 0
        // so we pass a high value here and update 'totalCount' after querying Solr with the correct value
        $pagination = new Pagination([
            'totalCount' => 50000000000
        ]);

        // Set the number of results to return
        $query->setRows($pagination->limit);

        // Set the 0-based result to start from
        $query->setStart($pagination->offset);

        // execute the query
        $result = Yii::$app->solr->select($query);

        // // Access debug information
        // $debugData = $result->getQuery()->getDebug();

        // // Print the actual Solr query
        // print_r($debugData);

        $response = $result->getData()['response'];

        $pagination->totalCount = $response['numFound'];

        // keep only paper ids from response
        $paper_ids = array_column($response['docs'], 'internal_id');

        return [
            $pagination,
            $paper_ids
        ];
    }

    public function prepareSearchResults($paper_ids) {

        // get paper details from the database, join with user likes
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        $rows = (new \yii\db\Query())
            ->select(['internal_id', 'dois_num', 'doi', 'openaire_id', 'title', 'authors', 'journal', 'year', 'type', 'is_oa', 'user_id', 'attrank', 'pagerank', '3y_cc', 'citation_count'])
            ->from('pmc_paper')
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where(['in', 'internal_id', $paper_ids])
            ->orderBy(addslashes($this->getRankingMethod($this->ordering)) . " DESC")
            ->all();

            return $rows;
    }

    public function search() {
        
        // prepare the search query
        $query = $this->prepareSearchQuery();

        // no articles found
        if (!$query) {
            return [ 'rows' => [] ];
        }

        // execute the search query
        [ $pagination, $paper_ids ] = $this->performSearchQuery($query);

        // get paper details from the database, join with user likes etc
        $rows = $this->prepareSearchResults($paper_ids);

        // highlight keywords in the content
        $rows = $this->get_context($rows);

        // add metadata (concepts, impact classes etc)
        $rows = $this->addMetadata($rows);

        return [
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    public function getTopicsFacet($limit = 5) {

        // prepare the search query
        $query = $this->prepareSearchQuery();
        if (!$query) {
            return [];
        }
        
        // do not return actual results, only facets
        $query->setRows(0);
        
        // set the facet field for topics
        $facetSet = $query->getFacetSet();
        $facetField = $facetSet->createFacetField('top_topics')
            ->setField('concepts')
            ->setLimit($limit)
            ->setMinCount(1); // exclude topics with no results

        // Execute the query
        $resultset =Yii::$app->solr->select($query);

        // Get the top 5 topics from the facet results
        $facet_values = $resultset->getFacetSet()->getFacet('top_topics')->getValues();

        return $facet_values;
    }

    public function getTopicEvolution($selected_topic) {

        // prepare the search query
        $query = $this->prepareSearchQuery();
        if (!$query) {
            return [];
        }
        
        // do not return actual results, only facets
        $query->setRows(0);

        // set the filter for the selected topic
        $selected_topic_condition = "concepts:" . $query->getHelper()->escapePhrase($selected_topic);
        $query->createFilterQuery('selected_topic_condition')->setQuery($selected_topic_condition);

        // Add faceting on year
        $facetSet = $query->getFacetSet();
        $facet = $facetSet->createFacetField('years_facet')
            ->setField('year')
            ->setMinCount(1);

        // Add statistics for citation counts
        $statsComponent = $query->getStats();
        $statsComponent->createField('citation_count')->addFacet('year');

        // Execute the query
        $resultset =Yii::$app->solr->select($query);

        // Get the top 5 topics from the facet results
        $count_per_year = $resultset->getFacetSet()->getFacet('years_facet')->getValues();   

        // Get the citation count statistics per year
        $stats = $resultset->getStats()->getResult('citation_count')->getFacets();
        $citation_per_year = isset($stats['year']) ? $stats['year'] : [];  

        // Determine the range of years
        $minYear = min(array_keys($count_per_year));
        $maxYear = max(array_keys($count_per_year));

        
        for ($year = $minYear; $year <= $maxYear; $year++) {

            // Fill missing years with zero for research works and citation counts
            if (!isset($count_per_year[$year])) {
                $count_per_year[$year] = 0;
            }

            // Fill missing years with zero for citation counts or get their sum
            if (!isset($citation_per_year[$year])) {
                $citation_per_year[$year] = 0;
            } else {
                $citation_per_year[$year] = $citation_per_year[$year]->getSum();
            }

        }

        // Sort the array by keys (years)
        ksort($count_per_year);
        ksort($citation_per_year);

        // Keep only the latest 20 values
        $count_per_year = array_slice($count_per_year, -20, 20, true);
        $citation_per_year = array_slice($citation_per_year, -20, 20, true);

        return [
            $count_per_year,
            $citation_per_year
        ];
    }

    public function addMetadata($rows) {

        // add the impact class of each row
        $rows = SearchForm::get_impact_class($rows);
        // get concepts and scores
        $rows = Concepts::getConcepts($rows, 'internal_id');
        // get impact scores per concept
        $rows = SearchForm::get_concepts_impact_class($rows);
        // get annotations
        $rows = Spaces::fetchAnnotations($rows, $this->space_model);
        // get relations
        $rows = Relations::getRelations($rows);

        return $rows;
    }

    public function searchLanguageForApi($protein_primary_citation, $page, $page_size) {

        // create a Solr select query
        $query = Yii::$app->solr->createSelect();

        // use dismax query parser and query specific fields
        $dismax = $query->getDisMax();
        $dismax->setQueryFields('title abstract authors');

        // return only paper ids
        $query->setFields(['internal_id']);

        // change to 'AND' as default query operator
        $query->setQueryDefaultOperator('AND');

        // set Solr query 'q' parameterer after escaping keywords
        $query->setQuery($this->keywords);

        // add appropriate filters
        if ($this->start_year != 0) {
            $query->createFilterQuery('star_year_filter')->setQuery('year:[' . $this->start_year . ' TO *]');
        }

        if ($this->end_year != 0) {
            $query->createFilterQuery('end_year_filter')->setQuery('year:[* TO ' . $this->end_year .']');
        }

        // get min impact scores to be added to query (set by impact category filter)
        $min_impact_scores = $this->getImpactScoreFilters();

        if ($this->influence != 'all') {
            $query->createFilterQuery('influence_filter')->setQuery('influence:[' . $min_impact_scores['influence'] . ' TO *]');
        }

        if ($this->popularity != 'all') {
            $query->createFilterQuery('popularity_filter')->setQuery('popularity:[' . $min_impact_scores['popularity'] . ' TO *]');
        }

        if ($this->impulse != 'all') {
            $query->createFilterQuery('impulse_filter')->setQuery('impulse:[' . $min_impact_scores['impulse'] . ' TO *]');
        }

        if ($this->cc != 'all') {
            $query->createFilterQuery('cc_filter')->setQuery('citation_count:[' . $min_impact_scores['cc'] . ' TO *]');
        }

        // if DOI of protein_primary_citation was found, exclude it from the results
        if (!empty($protein_primary_citation["doi"])) {
            $query->createFilterQuery('protein_primary_citation_filter')->setQuery('-doi:' . $query->getHelper()->escapePhrase($protein_primary_citation["doi"]));
        }

        // sort based on chosen impact
        $query->addSort($this->ordering, $query::SORT_DESC);

        // Set the number of results to return
        $query->setRows($page_size);

        // Set the 0-based result to start from
        $query->setStart( ($page - 1) * $page_size);

        // execute the query
        $result = Yii::$app->solr->select($query);
        $response = $result->getData()['response'];

        // keep only paper ids from response
        $paper_ids = array_column($response['docs'], 'internal_id');

        // get paper details from the database, join with user likes
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        $rows = (new \yii\db\Query())
            ->select(['internal_id', 'doi', 'title', 'abstract', 'authors', 'journal', 'year', 'attrank', 'pagerank', '3y_cc', 'citation_count'])
            ->from('pmc_paper')
            ->where(['in', 'internal_id', $paper_ids])
            ->orderBy(addslashes($this->getRankingMethod($this->ordering)) . " DESC")
            ->all();

        // highlight keywords in the content
        // $rows = $this->get_context($rows);

        // add the impact class of each row
        $rows = SearchForm::get_impact_class($rows);
        $result = [
            'meta' => [
                'total_count' => $response['numFound'],
                'page' => intval($page),
                'page_size' => intval($page_size)
            ]
        ];

        if (!empty($protein_primary_citation)) {
            $result['rcsb_primary_citation'] = $protein_primary_citation;
        }

        $result["rows"] = $rows;

        return $result;
    }

    public function searchById($id)
    {
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);
        //Build the search query.
        $query = (new \yii\db\Query())
        ->select(['internal_id', 'doi', 'openaire_id', 'title', 'journal','year', 'pagerank', 'pagerank_normalized','attrank', 'attrank_normalized', '3y_cc', '3y_cc_normalized', 'citation_count', 'citation_count_normalized', 'user_id'])
        ->from('pmc_paper')
        ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
        ->where("internal_id=".$id);

        //Get the results of the page.
        $rows = $query->all();

        return $rows[0];
    }

    public function count_filters() {
        $count = 0;
        if (count($this->topics) > 0) $count++;
        if (count($this->type) > 0) $count++;
        if ($this->start_year != 0) $count++;
        if ($this->end_year != 0) $count++;
        if ($this->influence != 'all') $count++;
        if ($this->popularity != 'all') $count++;
        if ($this->impulse != 'all') $count++;
        if ($this->cc != 'all') $count++;

        return $count;
    }

    public static function getSimilarArticles($paper_id) {
        // create a Solr select query
        $query = Yii::$app->solr->createSelect();

        // add a query and morelikethis settings
        $query->setQuery('internal_id:"' . $paper_id . '"')
            ->getMoreLikeThis()
            ->setFields('title, abstract')  // these must be indexed in Solr with termVectors=true
            ->setMinimumTermFrequency(1)
            ->setCount(20);
        $query->addParam('json.nl', 'map');
        $query->setRows(1);

        $result = Yii::$app->solr->select($query);
        $mlt = $result->getMoreLikeThis();
        $response = $result->getData()['moreLikeThis'][$paper_id];
        $similar_paper_ids = array_column($response['docs'], 'internal_id');

        // get details for the similar papers
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        return (new \yii\db\Query())
            ->select('internal_id, doi, title, authors, journal, year, user_id')
            ->from('pmc_paper')
            // needed to show if already bookmarked
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where(['internal_id' => $similar_paper_ids])
            ->all();
    }

    public static function getReadings($dois) {
        $rows = (new \yii\db\Query())
            ->select(['doi', 'attrank', 'pagerank'])
            ->from('pmc_paper')
            ->where(['in', 'doi', $dois])
            ->all();

        $rows = SearchForm::get_impact_class($rows);
        return $rows;
    }

}
