<?php

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'BIP! Services';
$this->registerCssFile('@web/css/home.css');

?>

<div class="site-index">
    <div class="jumbotron">
        <h1>
            <a href="<?= Url::to(['blog/default/view', 'id' => 1]) ?>">
                <?= Html::img('@web/img/bip-minimal-10-years.png', ['class' => 'img-responsive center-block', 'width' => 200, 'title' => 'Celebrating 10 Years of BIP! Services']) ?>
            </a>
        </h1>
        <p style = "margin-top:-10px;">Amplifying valuable research</p>
        <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2 col-xs-10 col-xs-offset-1">
            <?php foreach (Yii::$app->params['services'] as $service) { ?>
                <a class="service-card btn btn-default btn-lg" role="button" href="<?= Url::to($service['url']) ?>">
                    <div class="service-header main-green">
                        <?= $service['label'] ?>
                    </div>

                    <div class="service-description grey-text">
                        <?= $service['description'] ?>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>
</div>
