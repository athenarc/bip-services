<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use app\components\CustomBootstrapCheckboxList;

$this->title = 'Login';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container site-login">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php if($model->postMsg != '' && $model->postMsg != null): ?>
        <?= "<p class='text-warning'>$model->postMsg</p>" ?>
    <?php endif; ?>

    <p>Please fill out the following fields to login:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

    <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'maxlength' => 30, 'class' => 'search-box  form-control']) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => 50, 'class' => 'search-box  form-control']) ?>
    <div class="col-lg-offset-1"><a href="<?= Url::to(['site/requestreset']) ?>" class="main-green">Forgot Password?</a></div> 

    <?=  CustomBootstrapCheckboxList::widget(['name' => 'rememberMe', 'model' => $model, 'form' => $form, 'item_class' => 'checkbox checkbox-custom checkbox-inline']); ?>
 
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Log In', ['class' => 'btn btn-custom-color col-lg-3', 'name' => 'login-button']) ?>
        </div>
    </div>

    <div class="col-lg-offset-1 col-lg-11">
        Don't have an account? <a href="<?= Url::to(['site/signup']) ?>" class="main-green">Sign up</a>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="orcid-login" style="margin-top: 5em;">
        <p>Or login with <?= Html::a('ORCID <i class="fa-brands fa-orcid" title="Show profile on ORCID"></i>', ['site/orcid-login'], ['class' => 'btn btn-default']) ?></p>

    </div>
</div>
