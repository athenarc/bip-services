<?php

/*
 * Define the namespace of the widget
 */

namespace app\components;

/*
 * Includes
 */
use yii\base\Widget;
use app\models\Spaces;

/*
 * The widget class
 */
class TopAnnotationsItem extends Widget {
    public $space_url_suffix;

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
        // Get annotation types for dropdown
        $annotation_types = [];
        if ($this->space_url_suffix) {
            $space_model = Spaces::findOne(['url_suffix' => $this->space_url_suffix]);
            if ($space_model && !empty($space_model->annotations)) {
                $annotation_types = $space_model->getEnabledAnnotationMap();
            }
        }

        return $this->render('top_annotations_item', [
            'annotation_types' => $annotation_types,
        ]);
    }
}


