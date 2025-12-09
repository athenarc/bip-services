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
class FacetsItem extends Widget
{
    /*
     * Widget properties
     */


    public $edit_perm;
    public $result;
    public $formId;

    public $selected_topics;
    // public $selected_tags;
    public $selected_roles;
    public $selected_accesses;
    public $selected_types;

    public $current_cv_narrative;
    public $researcher;

    public $element_config;
    public $for_print;
    public $selected_per_list;
    public $facets_linked_to_lists;
    
    // additional data needed for logic
    public $contributions_lists;
    public $contributions_selected_filters;


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
        // handle linked contribution lists logic
        if ($this->for_print && $this->contributions_lists !== null) {
            $linkedListId = null;
            if (is_array($this->element_config)) {
                foreach ($this->element_config as $facetConfig) {
                    if (is_array($facetConfig) && isset($facetConfig['linked_contribution_element_id'])) {
                        $linkedListId = $facetConfig['linked_contribution_element_id'];
                        break;
                    }
                }
            }

            if ($linkedListId !== null && isset($this->contributions_lists[$linkedListId])) {
                $this->result = $this->contributions_lists[$linkedListId];
            } elseif (!empty($this->contributions_lists)) {
                $this->result = reset($this->contributions_lists);
            } else {
                $this->result = ['facets' => []];
            }

            $this->selected_topics = [];
            $this->selected_roles = [];
            $this->selected_accesses = [];
            $this->selected_types = [];
            
            if ($linkedListId !== null && isset($this->contributions_selected_filters[$linkedListId])) {
                $selectedFilters = $this->contributions_selected_filters[$linkedListId];
                $this->selected_topics = $selectedFilters['topics'] ?? [];
                $this->selected_roles = $selectedFilters['roles'] ?? [];
                $this->selected_accesses = $selectedFilters['accesses'] ?? [];
                $this->selected_types = $selectedFilters['types'] ?? [];
            }
        } else {
            // Check if this Facets box is linked to a specific Contributions List (non-PDF)
            $linked_id = $this->element_config['linked_contribution_element_id'] ?? null;

            if ($linked_id && isset($this->result['contributions_lists'][$linked_id])) {
                // Overwrite result to only use the specific contribution list works
                $this->result['papers'] = $this->result['contributions_lists'][$linked_id]['works'] ?? [];
                $this->result['topics'] = $this->result['contributions_lists'][$linked_id]['topics'] ?? [];
                $this->result['roles'] = $this->result['contributions_lists'][$linked_id]['roles'] ?? [];
                $this->result['accesses'] = $this->result['contributions_lists'][$linked_id]['accesses'] ?? [];
                $this->result['types'] = $this->result['contributions_lists'][$linked_id]['types'] ?? [];
            }
        }

        $data =[
            'edit_perm' => $this->edit_perm,
            'result' => $this->result,
            'formId' => $this->formId,
            'selected_topics' => $this->selected_topics,
            'selected_roles' => $this->selected_roles,
            'selected_accesses' => $this->selected_accesses,
            'selected_types' => $this->selected_types,
            'current_cv_narrative' => $this->current_cv_narrative,
            'researcher' => $this->researcher,
            'element_config' => $this->element_config,
            'selected_per_list' => $this->selected_per_list,
        ];

        if ($this->for_print) {
            return $this->render('pdf/facets_item', $data);
        }      
        return $this->render('facets_item', $data);
    }

}

?>