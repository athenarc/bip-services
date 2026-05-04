<?php

/*
 * Define the namespace of the widget
 */

namespace app\components;

/*
 * Includes
 */
use app\models\Spaces;
use yii\base\Widget;
use yii\web\View;

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
        $view = $this->getView();
        $view->registerCssFile('@web/css/top_annotations.css');
        $view->registerJsFile(
            '@web/js/components/topAnnotationsInit.js',
            ['depends' => 'yii\web\JqueryAsset', 'position' => View::POS_HEAD]
        );

        // Get annotation types for dropdown (only facet-enabled)
        $annotation_types = [];

        if ($this->space_url_suffix) {
            $space_model = Spaces::findOne(['url_suffix' => $this->space_url_suffix]);

            if ($space_model && ! empty($space_model->facetAnnotations)) {
                $annotation_types = $space_model->getFacetAnnotationMap();
            }
        }

        return $this->render('top_annotations_item', [
            'annotation_types' => $annotation_types,
        ]);
    }
}
