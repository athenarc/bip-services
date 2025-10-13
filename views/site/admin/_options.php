<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var int $threshold */

$this->title = 'Options';
?>

<div class="thresholds-settings">

    <nav aria-label="breadcrumb" style="position: relative;">
        <ol class="breadcrumb breadcrumb-admin mb-0">
            <li class="breadcrumb-item">
                <?= Html::a('Options', Url::to(['site/admin-options'])) ?>
            </li>
        </ol>
    </nav>

    <div class="panel panel-default" style="margin-top: 20px;">
        <div class="panel-heading">AI Usage</div>
        <div class="panel-body">
            <p class="grey-text">This value determines how many times a user can click the "Summarize" button.</p>

            <?php $form = ActiveForm::begin([
                'action' => Url::to(['site/admin-options']),
                'method' => 'post',
            ]); ?>

            <div class="form-group">
                <?= Html::label('Maximum summarize clicks per user', 'threshold', ['class' => 'control-label grey-text']) ?>
                <?= Html::input('number', 'threshold', $threshold, [
                    'class' => 'form-control input-sm',
                    'style' => 'max-width: 50px; display: inline-block; ',
                    'min' => 0
                ]) ?>
            </div>

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-sm btn-custom-color']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
