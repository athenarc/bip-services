<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;

$this->title = 'BIP! Services - Readings';

?>

<div class = "row">
    <div class = "col-sm-8">
        <h2 class="green-bip">Readings</h2>
    </div>
    <div class = "col-sm-4">
        <h2 class = "text-right">
            <a class = "btn btn-default"href="<?= Url::to(['readings/list']) ?>">
            <?php
                if ( !isset($user_id) ) {
                    echo "Sign in to manage your readings";
                } else {
                    echo "My readings";
                }
            ?>
            </a>
        </h2>
    </div>
</div>

<div class = "row">
    <div class ="flex-wrap">
        <div class="col-md-7">
            <p class = "help-text">
                Welcome to BIP! Readings, a platform for organising
                and accessing all your bookmarked research papers.
                With BIP! Readings, you can easily create and manage your own reading
                lists and share them with others.
            </p>
            <p class = "help-text">
                BIP! Readings allows you to conveniently store all your important
                research papers in one place, making it easier to access
                them when you need them most.
                Whether you are a student, researcher, or simply someone who enjoys reading scholarly articles,
                BIP! Readings can help you organise and prioritise your reading.
                With its intuitve user interface,
                you can create reading lists tailored to your interests and share them with others
                in just a few clicks.
            </p>
        </div>
        <div class = "col-md-5">
            <?= Html::img("@web/img/readings_example.png", ['alt' => 'Search example', 'class' => 'img-responsive screenshot', 'style' => 'margin:0']) ?>
        </div>
    </div>
</div>

