<?php

namespace app\components;

use yii\base\Widget;

/**
 * Widget for displaying synonyms found through annotation expansion.
 */
class Synonyms extends Widget {
    /**
     * @var array Array of synonym expansions, each with 'display_name' and 'synonyms'
     */
    public $synonyms_expansions = [];

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
     * Running the widget.
     */
    public function run() {
        if (empty($this->synonyms_expansions)) {
            return '';
        }

        return $this->render('synonyms', [
            'synonyms_expansions' => $this->synonyms_expansions,
            'space_url_suffix' => $this->space_url_suffix,
            'current_keywords' => $this->current_keywords,
            'current_params' => $this->current_params,
        ]);
    }
}
