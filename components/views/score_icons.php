<?php
use yii\helpers\Html;
?>
<!-- Impact header -->
<div class="citation-scores" style="margin: 3px 0 4px 0; display: flex; gap: 5px; flex-wrap: wrap; align-items: center;">
    <div style="font-weight: bold; font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080; margin-left: 6px;">
        Impact:
    </div>

    <!-- Popularity -->
    <div class="score-item" style="display: flex; align-items: center; gap: 5px;">
        <span title="Popularity">
            <i class="fa fa-fire" aria-hidden="true" style="color: #439d44; font-size: 18px; vertical-align: middle;"></i>
        </span>
        <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;">
            <?= isset($pop) ? $pop : '-' ?>
        </span>
    </div>

    <!-- Influence -->
    <div class="score-item" style="display: flex; align-items: center; gap: 5px; margin-left: 15px;">
        <span class="impact-icon impact-icon-A" title="Influence">
            <i class="fa fa-university" aria-hidden="true" style="color: #439d44; font-size: 18px; vertical-align: middle;"></i>
        </span>
        <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;">
            <?= isset($inf) ? $inf : '-' ?>
        </span>
    </div>

    <!-- Citation Count -->
    <div class="score-item" style="display: flex; align-items: center; gap: 5px; margin-left: 15px;">
        <span class="impact-icon impact-icon-A" title="Citation Count">
            <i class="fa fa-quote-left" aria-hidden="true" style="color: #439d44; font-size: 18px; vertical-align: middle;"></i>
        </span>
        <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;">
            <?= isset($cc) ? $cc : '-' ?>
        </span>
    </div>

    <!-- Impulse -->
    <div class="score-item" style="display: flex; align-items: center; gap: 5px; margin-left: 15px;">
        <span class="impact-icon impact-icon-A" title="Impulse">
            <i class="fa fa-rocket" aria-hidden="true" style="color: #439d44; font-size: 18px; vertical-align: middle;"></i>
        </span>
        <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;">
            <?= isset($imp) ? $imp : '-' ?>
        </span>
    </div>
</div>