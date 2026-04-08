<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'BIP! Services - Readings';
$this->registerCssFile('@web/css/readings-landing.css');

?>

<div id="readings-landing">
    <div class="row">
        <div class="col-xs-12 text-center">
            <h1>BIP! Readings</h1>
        </div>
    </div>

    <div class="readings-landing-banner">
        <?= Html::img('@web/img/readings/bip-readings.jpg', [
            'alt' => 'BIP! Readings',
            'class' => 'img-responsive readings-landing-banner__img',
            'width' => 1536,
            'height' => 1024,
        ]) ?>
        <div class="readings-landing-banner__actions" role="toolbar" aria-label="Readings quick actions">
            <div class="row readings-landing-banner__actions-row">
                <div class="col-xs-12 text-right readings-landing-banner__profile-col">
                    <a href="<?= Url::to(['readings/list']) ?>" class="btn btn-default readings-landing-banner__btn readings-landing-banner__profile-btn">
                        <?php
                        if (! isset($user_id)) {
                            echo 'Sign in to manage your readings';
                        } else {
                            echo 'My readings';
                        }
                        ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="readings-landing-content">
        <h2 class="readings-landing-title help-text">Your research readings, organized.</h2>
        <div class="card">
            <div class="card-body">
                <p class="card-text help-text">
                    BIP! Readings lets researchers create curated reading lists around topics of interest, organize
                    relevant articles, and add personal notes to capture insights. Lists can be easily shared with
                    others, supporting collaboration and knowledge exchange. Currently available in alpha.
                </p>
            </div>
        </div>
    </div>
</div>
