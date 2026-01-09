<?php

namespace app\components;

use yii\base\Widget;

/**
 * SummaryPanel widget
 * Renders the AI summary panel with controls for generating and copying summaries.
 */
class SummaryPanel extends Widget {
    /**
     * Widget initialisation.
     */
    public function init() {
        parent::init();
    }

    /**
     * Running the widget.
     */
    public function run() {
        return $this->render('summary_panel');
    }
}
