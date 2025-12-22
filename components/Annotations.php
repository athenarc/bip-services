<?php

/*
 * Widget for displaying annotations grouped by type
 *
 * (First version: Dec 2024)
 */

namespace app\components;

use yii\base\Widget;

/**
 * The widget class for displaying annotations
 */
class Annotations extends Widget
{
    /*
     * Widget properties
     */
    public $annotations;
    public $internal_id;
    public $space_annotation_db;
    public $space_url_suffix;
    public $enable_like_dislike_annotations;

    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
        
        // Register CSS file
        $this->view->registerCssFile('@web/css/components/annotations.css', [
            'depends' => [\yii\bootstrap\BootstrapAsset::className()]
        ]);
        
        // Register JavaScript file (Yii handles duplicate prevention automatically)
        $this->view->registerJsFile('@web/js/components/annotations.js', [
            'depends' => [\yii\web\JqueryAsset::className()],
            'position' => \yii\web\View::POS_END
        ]);
    }

    /*
     * Running the widget a.k.a. rendering results
     */
    public function run()
    {
        if (empty($this->annotations) || !is_array($this->annotations)) {
            return '';
        }

        // Group annotations by annotation_id (type)
        $grouped_annotations = [];
        foreach ($this->annotations as $annotation) {
            // Skip if annotation is not an array or annotation_id is missing or null
            if (!is_array($annotation) || !isset($annotation['annotation_id']) || $annotation['annotation_id'] === null) {
                continue;
            }
            $annotation_id = $annotation['annotation_id'];
            if (!isset($grouped_annotations[$annotation_id])) {
                $grouped_annotations[$annotation_id] = [
                    'type_name' => $annotation['annotation_description'] ?? 'Annotation',
                    'type_color' => $annotation['annotation_color'] ?? '#000000',
                    'items' => []
                ];
            }
            $grouped_annotations[$annotation_id]['items'][] = $annotation;
        }

        if (empty($grouped_annotations)) {
            return '';
        }

        return $this->render('annotations', [
            'grouped_annotations' => $grouped_annotations,
            'internal_id' => $this->internal_id,
            'space_annotation_db' => $this->space_annotation_db,
            'space_url_suffix' => $this->space_url_suffix,
            'enable_like_dislike_annotations' => $this->enable_like_dislike_annotations ?? false,
        ]);
    }
}

