<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;


$this->title = 'Change Password';
?>
<div class="container site-about">
    <h1><?= Html::encode($this->title) ?></h1></br>
    

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'oldPassword')->passwordInput([
            'class' => 'search-box form-control narrow-input',
            'placeholder' => 'Enter current password'
        ]) ?>

        <?= $form->field($model, 'newPassword')->passwordInput([
            'class' => 'search-box form-control narrow-input',
            'placeholder' => 'Enter new password'
        ]) ?>

        <?= $form->field($model, 'confirmPassword')->passwordInput([
            'class' => 'search-box form-control narrow-input',
            'placeholder' => 'Confirm new password'
        ]) ?>
        </br>
        <div class="form-group">
            <?= Html::submitButton('Change Password', ['class' => 'btn btn-custom-color']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>

