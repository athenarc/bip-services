<?php

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'BIP! Services';
$this->registerCssFile('@web/css/home.css');

?>

<div class="site-index">
    <div class="jumbotron">
        <h1><?= Html::img("@web/img/bip-minimal.png", ['class' => 'img-responsive center-block']) ?></h1>
        <p style = "margin-top:-10px;">Amplifying valuable research</p>
    </div>
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
