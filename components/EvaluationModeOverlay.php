<?php

namespace app\components;

use Yii;
use yii\base\Widget;

/**
 * Small diagonal ribbon overlay to indicate that a special evaluation mode is active.
 *
 * Usage:
 *   In a view (e.g. Finder index) set:
 *     $this->params['evaluationModeActive'] = true;
 *
 *   In the main layout:
 *     <?= EvaluationModeOverlay::widget(['active' => $this->params['evaluationModeActive'] ?? false]) ?>
 */
class EvaluationModeOverlay extends Widget {
    /**
     * Whether the overlay should be visible.
     *
     * @var bool
     */
    public $active = false;

    /**
     * Optional label to display inside the ribbon.
     *
     * @var string
     */
    public $label = 'Evaluation Mode';

    /**
     * Optional background color (e.g. hex) for the ribbon.
     *
     * @var string|null
     */
    public $color;

    public function run() {
        if (! $this->active) {
            return '';
        }

        return $this->render('evaluation-mode-overlay', [
            'label' => $this->label,
            'color' => $this->color,
        ]);
    }
}


