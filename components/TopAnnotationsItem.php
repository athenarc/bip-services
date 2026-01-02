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
class TopAnnotationsItem extends Widget {
    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init() {
        parent::init();
    }

    /*
     * Running the widget
     */
    public function run() {
        return $this->render('top_annotations_item');
    }
}


