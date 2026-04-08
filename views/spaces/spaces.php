<?php

use yii\helpers\Html;

$this->title = 'BIP! Services - Spaces';
$this->registerCssFile('@web/css/spaces-landing.css');

?>

<div id="spaces-landing">
    <div class="row">
        <div class="col-xs-12 text-center">
            <h1>BIP! Spaces</h1>
        </div>
    </div>

    <div class="spaces-landing-banner">
        <?= Html::img('@web/img/spaces/bip-spaces.jpg', [
            'alt' => 'BIP! Spaces',
            'class' => 'img-responsive spaces-landing-banner__img',
            'width' => 1536,
            'height' => 1024,
        ]) ?>
    </div>

    <div class="spaces-landing-content">
        <h2 class="spaces-landing-title help-text">Your knowledge. Your search.</h2>
        <div class="card">
            <div class="card-body">
                <p class="card-text help-text">
                    Each BIP! Space is an academic search engine tailored for the needs of a specific organisation
                    or community. The owner of the space can choose a custom theme for the engine and modify
                    its default behavior (e.g., default ranking method and filters applied to searches).
                </p>
                <p class="card-text help-text">
                    More importantly, each space is connected with a knowledge base (e.g., a knowledge graph)
                    that encodes annotations related to the domain of interest determined by the organisation or
                    community that owns it.
                </p>
                <p class="card-text help-text">
                    A BIP! Space is created upon request (if you are interested, please send an email at
                    <a href="mailto:bip@athenarc.gr" class="main-green">bip@athenarc.gr</a>).
                </p>
            </div>
        </div>
    </div>
</div>
