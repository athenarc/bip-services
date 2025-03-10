<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FeedbackForm */

$this->title = 'Submit Feedback';
?>
<div class="feedback-form">
    <h2><?= Html::encode($this->title) ?></h2>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'email')->textInput(['class' => 'search-box form-control', 'value' => Yii::$app->user->identity->email, 'readonly' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['class' => 'search-box form-control']) ?>
    <?= $form->field($model, 'description')->textarea(['class' => 'search-box form-control', 'rows' => 5, 'style' => 'resize: vertical;']) ?>
    <?= $form->field($model, 'category')->dropDownList([
        'bug' => 'Bug',
        'new feature proposal' => 'New Feature Proposal',
        'suggestion' => 'Suggestion',
        'user account issue' => 'User Account Issue'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Submit Feedback', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
