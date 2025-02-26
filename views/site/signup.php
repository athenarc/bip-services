<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\captcha\Captcha;

$auth_provider = Yii::$app->session->get('auth_provider');
$auth_id = Yii::$app->session->get('auth_id');

$this->title = 'Sign Up' . (($auth_provider) ? ' with ' . $auth_provider : '');

?>
<div class="container site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to sign up:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'signup-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'maxlength' => 30, 'class' => 'search-box form-control']) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => 50, 'class' => 'search-box form-control']) ?>

        <?php if (!$auth_id): ?>
            <?= $form->field($model, 'password')->passwordInput(['maxlength' => 50, 'class' => 'search-box form-control']) ?>
        <?php else: ?>
            <?= $form->field($model, 'auth_provider')->textInput(['value' => $auth_provider, 'readonly' => true, 'class' => 'search-box form-control']) ?>
            <?= $form->field($model, 'auth_id')->textInput(['value' => $auth_id, 'readonly' => true, 'class' => 'search-box form-control']) ?>
        <?php endif; ?>

        <?= $form->field($model, 'captcha')->widget(Captcha::className()) ?>
        <p class="col-md-offset-1 status-message">Click on the captcha image to refresh it.</p>

        <?= /*$form->field($model, 'rememberMe')->checkbox([
            'template' => "<div class=\"col-lg-offset-1 col-lg-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
        ])*/"" ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Sign Up', ['class' => 'btn btn-custom-color col-lg-3', 'name' => 'signup-button']) ?>
            </div>
        </div>
        You can review our Privacy and Personal Data Settings  <a href="<?= Url::toRoute(['site/data-policy#personal_data_settings']) ?>" class="main-green">here</a>.

    <?php ActiveForm::end(); ?>
    
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>
</div>
