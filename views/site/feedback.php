<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FeedbackForm */

$this->title = 'Submit feedback';
?>
<div class="feedback-form">
    <h2><?= Html::encode($this->title) ?></h2>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['class' => 'search-box form-control']) ?>
    <?= $form->field($model, 'category')->dropDownList([
        'general inquiry' => 'General inquiry',
        'bug or problem' => 'Bug or problem',
        'new feature proposal' => 'New feature proposal',
        'suggestion' => 'Suggestion',
        'user account issue' => 'User account issue'
    ]) ?>
    <?= $form->field($model, 'description')->textarea(['class' => 'search-box form-control', 'rows' => 5, 'style' => 'resize: vertical;']) ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
