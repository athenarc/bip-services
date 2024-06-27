<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\helpers\Url;
use kartik\sortable\Sortable;
use yii\bootstrap\Modal;

/** @var yii\web\View $this */
/** @var app\models\ProfileTemplateCategories $model */
/** @var yii\widgets\ActiveForm $form */

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceAdminPanel.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

if ($templateCategoryModel->isNewRecord)
    $this->title = 'Create Template Category';
else
    $this->title = 'Update Template Category: ' . $templateCategoryModel->name;

// $this->params['breadcrumbs'][] = ['label' => 'Profile Template Categories', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>

<div class="profile-template-categories-create-update">

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

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="profile-template-categories-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($templateCategoryModel, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($templateCategoryModel, 'description')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>

        <div class="form-group">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-danger']) ?>
            <?php if ($templateCategoryModel->isNewRecord): ?>
                <?= Html::a('Back', ['admin-profiles'], ['class' => 'btn btn-default']) ?>
            <?php else: ?>
                <?= Html::a('Back', ['view-template-category', 'id' => $templateCategoryModel->id], ['class' => 'btn btn-default']) ?>
            <?php endif ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
