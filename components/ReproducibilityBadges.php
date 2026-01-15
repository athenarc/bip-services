<?php

/*
 * Widget for displaying reproducibility readiness badges:
 *
 * @params:
 * has_dataset: boolean indicating if datasets are available
 * has_software: boolean indicating if software tools are available
 *
 * (First version: January 2025)
 */

// ---------------------------------------------------------------------------- #

/*
 * Define the namespace of the widget
 */

namespace app\components;

/*
 * Includes
 */

use Yii;
use yii\base\Widget;

/*
 * The widget class
 */

class ReproducibilityBadges extends Widget {
    /*
     * Widget properties
     */

    public $has_dataset;

    public $has_software;

    /*
     * Widget initialisation
     */
    public function init() {
        parent::init();
    }

    /*
     * Running the widget a.k.a. rendering results
     */
    public function run() {
        // Only render if at least one badge should be shown
        if (!$this->has_dataset && !$this->has_software) {
            return '';
        }

        return $this->render('reproducibility_badges', [
            'has_dataset' => $this->has_dataset,
            'has_software' => $this->has_software,
        ]);
    }
}
