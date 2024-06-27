<?php

// Renders the message displayed inside the concepts' popover

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
class ConceptPopover extends Widget
{
    /*
     * Widget properties
     */

    public $concept;


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
        return $this->render('concept_popover');
    }

}

