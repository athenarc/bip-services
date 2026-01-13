<?php

namespace app\components;

use yii\base\Widget;

/**
 * Widget for displaying synonyms found through annotation expansion
 */
class Synonyms extends Widget {
    /**
     * @var array Array of synonym labels to display
     */
    public $synonyms = [];

    /**
     * @var string|null Space URL suffix for building search links
     */
    public $space_url_suffix = null;

    /**
     * @var string|null Current search keywords to expand
     */
    public $current_keywords = null;

    /**
     * @var array Current search parameters (filters, ordering, etc.) to preserve
     */
    public $current_params = [];

    /**
     * @var string|null Entity name (e.g., "Disease") to display in the message
     */
    public $entity_name = null;

    /**
     * Widget initialisation
     */
    public function init() {
        parent::init();
    }

    /**
     * Running the widget
     */
    public function run() {
        if (empty($this->synonyms)) {
            return '';
        }

        return $this->render('synonyms', [
            'synonyms' => $this->synonyms,
            'space_url_suffix' => $this->space_url_suffix,
            'current_keywords' => $this->current_keywords,
            'current_params' => $this->current_params,
            'entity_name' => $this->entity_name,
        ]);
    }
}
