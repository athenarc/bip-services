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
class AnnotationPopover extends Widget {
    /*
     * Widget properties
     */

    public $data;

    public $space_annotation_db;

    public $annotation_type_id;

    public $space_url_suffix;

    public $paper_id;

    public $annotation_name;

    public $annotation_id; // Local annotation identifier

    public $enable_like_dislike_annotations;

    public $user_vote_annotation; // 'like', 'dislike', or null

    public $has_graph_entity_fields = false; // Whether graph entity fields are configured

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
        return $this->render('annotation_popover');
    }
}
