<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\AssessmentFrameworks $frameworkModel */
/** @var yii\widgets\ActiveForm $form */

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceAdminPanel.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>

<div class="assessment-frameworks-create-update">

    <ul class="nav nav-tabs green-nav-tabs" style = "margin-bottom: 30px;">
        <li class="<?= $section_overview == "overview" ? 'active' : ''?>">
        <a class="" <?= !$section_overview ? "href=" . Url::to(['site/admin-overview']) : "" ?>>Overview</a>
        </li>
        <li class="<?= $section_spaces ? 'active' : ''?>">
        <a class="" <?= !$section_spaces ? "href=" . Url::to(['site/admin-spaces']) : "" ?>>Spaces</a>
        </li>
        <li class="<?= $section_scholar ? 'active' : ''?>">
        <a class="" <?= !$section_scholar ? "href=" . Url::to(['site/admin-scholar']) : "" ?>>Scholar</a>
        </li>
        <li class="<?= $section_indicators ? 'active' : ''?>">
        <a class="" <?= !$section_indicators ? "href=" . Url::to(['site/admin-indicators']) : "" ?>>Indicators</a>
        </li>
        <li class="<?= $section_profiles ? 'active' : ''?>">
        <a class="" <?= !$section_profiles ? "href=" . Url::to(['site/admin-profiles']) : "" ?>>Profiles</a>
        </li>
    </ul>

    <?php if ($frameworkModel->isNewRecord): ?>
        <h1>Create Assessment Framework</h1>
    <?php else: ?>
        <h1>Update Framework: <?= $frameworkModel->name ?></h1>
    <?php endif ?>

    <div class="assessment-frameworks-form">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($frameworkModel, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($frameworkModel, 'webpage')->textInput(['maxlength' => true]) ?>
        <?= $form->field($frameworkModel, 'description')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-danger']) ?>
            <?= Html::a('Back', ['view-framework', 'id' => $frameworkModel->id], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
