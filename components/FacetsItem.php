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
        ];

        if ($this->for_print) {
            return $this->render('pdf/facets_item', $data);
        }
        return $this->render('facets_item', $data);
    }

}

?>