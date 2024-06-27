<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Forgot Password';
//$this->params['breadcrumbs'][] = $this->title;

?>

<div class="container site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'requestreset-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => 50, 'class' => 'search-box form-control']) ?>
    
     <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Password Reset', ['class' => 'btn btn-success', 'name' => 'request-reset-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
