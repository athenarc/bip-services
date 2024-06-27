<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "paper_citation_histories".
 *
 * @property integer $pmc
 * @property integer $cc
 * @property integer $year
 */
class PaperCitationHistories extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'citations_per_year';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['internal_id', 'cc', 'year'], 'integer'],
            // [['pmc'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'internal_id' => 'id',
            // 'pmc' => 'pmc',
            'cc' => 'cc',
            'year' => 'year',
        ];
    }
    
    public function getPaper()
    {
        return $this->hasOne(Article::className(), ['internal_id' => 'internal_id']);
    }
    
    /*
     * Format citation history arrays
     * by: Ilias Kanellos
     */
    public static function formatCitationHistory($history_table)
    {
        //Get the range of years for this paper
        $max_year = max(array_keys($history));
        $min_year = min(array_keys($history));
        $range_array = range($min_year, $max_year);
        //Add zero cc value for years that were not recorded in database
        foreach ($range_array as $year)
        {
            if (!array_key_exists($year, $history))
            {
                $history[$year] = 0;
            }
        }
        //Return array sorted by keys (years)
        ksort($history);
        //Return it
        return $history;
        
    }

    public static function forArticleComparison($article_id, $domain_min_x, $domain_max_x, $domain_max_y) {
        $article_citation_history = Article::findOne($article_id)->getCitationHistory()->asArray()->all();
                
        // Get years and citations for paper to find min/max values
        $current_years = array_column($article_citation_history, 'year');
        $current_ccs   = array_column($article_citation_history, 'cc');

        // Get domains for years and cc
        if($domain_min_x == null)
        {
            $domain_min_x = min($current_years);
        }
        if($domain_max_x == null)
        {
            $domain_max_x = max($current_years);
        }
        if($domain_max_y == null)
        {
            $domain_max_y = max($current_ccs);
        }
        
        $current_min_year = min($current_years);
        $current_max_year = max($current_years);
        $current_max_cc   = max($current_ccs);

        //Check if we need to replace them
        $domain_min_x >  $current_min_year ? $domain_min_x = $current_min_year : $domain_min_x;
        $domain_max_x <  $current_max_year ? $domain_max_x = $current_max_year : $domain_max_x;
        $domain_max_y <  $current_max_cc   ? $domain_max_y = $current_max_cc   : $domain_max_y;
        
        return [
            'article_citation_history' => $article_citation_history, 
            'domain_min_x' => $domain_min_x, 
            'domain_max_x' => $domain_max_x, 
            'domain_max_y' => $domain_max_y
        ];
    }

    public static function formatForComparison($paper_citation_histories, $domain_max_x) {
        foreach ($paper_citation_histories as $article_id => $values)
        {
            //For all years that are not in the database, it means our paper has zero citations
            //We need to explicitly add these years and CCs for d3 to work with our data.
            $current_paper_start_year = $paper_citation_histories[$article_id][0]["year"];
            $years_in_range = array();
            //Create an array with the range of years for the particular article
            $years_range = range($current_paper_start_year, $domain_max_x);
            //Record the years that DO have values
            foreach ($paper_citation_histories[$article_id] as $entry)
            {
                $year = $entry["year"];
                array_push($years_in_range, $year);
            }
            //Find the missing years
            $years_missing = array_diff($years_range, $years_in_range);
            //Add a record with zero CC at each missing year
            foreach ($years_missing as $missing_year)
            {
                $missing_year_offset = array_search($missing_year, $years_range);
                array_splice($paper_citation_histories[$article_id], $missing_year_offset, 0, array(array("year" => $missing_year, "cc" => 0)));
            }
            
            //Add first record at cc = 0 regardless of other values, to correctly close the area
            $first_record = $paper_citation_histories[$article_id][0];
            $first_record['cc'] = 0;
            array_unshift($paper_citation_histories[$article_id], $first_record);
            
            //Add final record at cc = 0 regardless of others to correctly close the line area
            array_push($paper_citation_histories[$article_id], array('year' => $domain_max_x, 'cc' => 0));
        }
        return $paper_citation_histories;
    }
}
