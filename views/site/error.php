<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="container site-error">
    <?php if ($exception->statusCode == 404) { ?>
        <?= Html::img("@web/img/bip_404.png", ['alt' => '404', 'class' => 'img-responsive center-block' ]) ?>


    <? } else { ?>
        <h1>Holy BIP!</h1>

        <div class="alert alert-danger">
            <strong><?= Html::encode($this->title) ?>:</strong> <?= nl2br(Html::encode($message)) ?>
        </div>

        <p>
            The above error occurred while the Web server was processing your request.
        </p>
        <p>
            Please contact us if you think this is a server error. Thank you.
        </p>
    <?php } ?>
</div>
