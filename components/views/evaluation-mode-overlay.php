<?php

/* @var $label string */
/* @var $color string|null */

use yii\helpers\Html;

echo Html::cssFile('@web/css/components/evaluation-mode-overlay.css');

// Build inline style for dynamic background color if provided
$style = '';

if (! empty($color)) {
    $safeColor = Html::encode($color);
    $style = "background-color: {$safeColor};";
}

?>

<div class="evaluation-mode-overlay"<?= $style ? ' style="' . $style . '"' : '' ?>>
    <div>
        <h4><?= Html::encode($label) ?></h4>
    </div>
</div>


