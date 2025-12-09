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
    public $for_print;

    public $current_cv_narrative;
    public $facets_for_this_list;
    public $selected_topics;
    public $selected_tags;
    public $selected_roles;
    public $selected_accesses;
    public $selected_types;
    public $preHeaderHtml;
    public $show_pagination;
    public $list_id;
    public $show_missing_works;
    public $noWorksMessage;
    public $contributions_lists;


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
        // handle contributions list logic
        if ($this->for_print && $this->contributions_lists !== null && $this->list_id !== null) {
            $listResult = $this->contributions_lists[$this->list_id] ?? [
                'papers' => [],
                'papers_num' => 0,
                'selected_papers' => [],
                'selected_papers_num' => 0,
                'all_papers' => [],
                'selected_accesses' => [],
                'selected_types' => [],
            ];

            $topK = isset($this->element_config['top_k']) && $this->element_config['top_k'] !== ''
                ? (int)$this->element_config['top_k']
                : null;

            $papersForPrint = [];

            if (!empty($listResult['selected_papers'])) {
                $papersForPrint = $listResult['selected_papers'];
            } elseif (!empty($listResult['all_papers'])) {
                $papersForPrint = $listResult['all_papers'];
            } elseif (!empty($listResult['papers'])) {
                $papersForPrint = $listResult['papers'];
            }

            if ($topK !== null && $topK > 0 && !empty($papersForPrint)) {
                $papersForPrint = array_slice($papersForPrint, 0, $topK);
            }

            // Override widget properties with computed values
            $this->result = $listResult;
            $this->papers = $papersForPrint;
            $this->works_num = count($papersForPrint);
            $this->show_missing_works = $listResult['show_missing_papers'] ?? true;
        }

        $data =[
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
            'heading_type' => $this->heading_type,
            'facets_for_this_list' => $this->facets_for_this_list,
            'selected_topics' => $this->selected_topics,
            'selected_tags' => $this->selected_tags,
            'selected_roles' => $this->selected_roles,
            'selected_accesses' => $this->selected_accesses,
            'selected_types' => $this->selected_types,
            'preHeaderHtml' => $this->preHeaderHtml,
            'show_pagination' => $this->show_pagination,
            'list_id' => $this->list_id,
            'show_missing_works' => $this->show_missing_works,
            'noWorksMessage' => $this->noWorksMessage,
        ];


        if ($this->for_print) {
            return $this->render('pdf/contributions_list_item', $data);
        }
        
        return $this->render('contributions_list_item', $data);
    }

}

?>