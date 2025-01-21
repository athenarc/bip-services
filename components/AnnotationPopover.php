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
class AnnotationPopover extends Widget
{
    /*
     * Widget properties
     */

    public $data;
    public $space_annotation_db;
    public $space_annotation_id;
    public $space_url_suffix;
    public $has_reverse_annotation_query;


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
        return $this->render('annotation_popover');
    }

}

