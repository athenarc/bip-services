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
class ContributionsListItem extends Widget
{
    /*
     * Widget properties
     */


    public $edit_perm;
    public $facets_selected;
    public $result;
    public $papers;
    public $heading_type;
    public $works_num;
    public $missing_papers;
    public $missing_papers_num;
    public $sort_field;
    public $orderings;
    public $formId;
    public $impact_indicators;
    public $element_config;

    public $current_cv_narrative;

    // public $show;


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
        return $this->render('contributions_list_item', [
            'impact_indicators' => $this->impact_indicators,
            'edit_perm' => $this->edit_perm,
            'facets_selected' => $this->facets_selected,
            'result' => $this->result,
            'papers' => $this->papers,
            'works_num' => $this->works_num,
            'missing_papers' => $this->missing_papers,
            'missing_papers_num' => $this->missing_papers_num,
            'sort_field' => $this->sort_field,
            'orderings' => $this->orderings,
            'formId' => $this->formId,
            'element_config' => $this->element_config,
            'current_cv_narrative' => $this->current_cv_narrative,
            'heading_type' => $this->heading_type
        ]);
    }

}

?>