<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* 
 * Form to reset user password
 * 
 * @author: Hlias
 */
$this->title = 'Password Reset';
//$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container site-login">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php if($model->postMsg != '' && $model->postMsg != null): ?>
    <?= "<p class='text-warning'>$model->postMsg</p>" ?>
    <?php endif; ?>
    <p>Please fill out the following fields:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'passreset-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

    
    <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'maxlength' => 30, 'class' => 'search-box form-control']) ?>
    <?= $form->field($model, 'newPass')->passwordInput(['maxlength' => 50, 'class' => 'search-box form-control']) ?>
    <?= $form->field($model, 'token', ['template' => '<div class="col-lg-11 col-lg-offset-1">{error}</div><div>{input}</div>', 'errorOptions' => ['encode' => false]])->hiddenInput(['value' => $model->token])->label(false); ?>
 
     <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Reset Password', ['class' => 'btn btn-custom-color', 'name' => 'request-reset-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    
</div>