<?php

/*
 * Define the namespace of the widget
 */
namespace app\components;

/*
 * Includes
 */
use yii\base\Widget;

/*
 * The widget class
 */
class ResultItem extends Widget
{
    /*
     * Widget properties
     */

    public $internal_id;
    public $edit_perm;
    public $doi;
    public $dois_num;
    public $openaire_id;
    public $title;
    public $authors;
    public $journal;
    public $year;
    public $user_id;
    public $reading_status;
    public $reading_status_choices;
    public $tags;
    public $notes;
    public $pop_score;
    public $inf_score;
    public $imp_score;
    public $cc_score;
    public $pop_class;
    public $inf_class;
    public $imp_class;
    public $cc_class;
    public $impact_indicators;
    // public $citations;
    public $show;
    public $involvements;
    public $involved;
    public $is_oa;
    public $type;
    public $search_context;
    public $concepts;
    public $annotations;
    public $relations;
    public $space_url_suffix;
    public $space_annotation_db;
    public $for_print;
    
    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
    }

    /*
     * Running the widget
     */
    public function run()
    {
        if ($this->for_print) {
            return $this->render('pdf/result_item');
        }
        return $this->render('result_item');
    }

}

?>