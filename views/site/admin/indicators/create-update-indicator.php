<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Indicators $indicatorModel */
/** @var yii\widgets\ActiveForm $form */

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceAdminPanel.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");

$back_url = ($indicatorModel->isNewRecord) ? ['site/admin-indicators'] : ['view-indicator', 'id' => $indicatorModel->id];

?>
<div class="indicators-create-update">

    <ul class="nav nav-tabs green-nav-tabs" style = "margin-bottom: 30px;">
        <li class="<?= $section_overview == "overview" ? 'active' : ''?>">
        <a class="" <?= !$section_overview ? "href=" . Url::to(['site/admin-overview']) : "" ?>>Overview</a>
        </li>
        <li class="<?= $section_spaces ? 'active' : ''?>">
        <a class="" <?= !$section_spaces ? "href=" . Url::to(['site/admin-spaces']) : "" ?>>Spaces</a>
        </li>
        <li class="<?= $section_indicators ? 'active' : ''?>">
        <a class="" <?= !$section_indicators ? "href=" . Url::to(['site/admin-indicators']) : "" ?>>Indicators</a>
        </li>
        <li class="<?= $section_profiles ? 'active' : ''?>">
        <a class="" <?= !$section_profiles ? "href=" . Url::to(['site/admin-profiles']) : "" ?>>Profile Templates</a>
        </li>
    </ul>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-admin">
            <li class="breadcrumb-item">Indicators</li>
            <?php if ($indicatorModel->isNewRecord): ?>
                <li class="breadcrumb-item">new</li> 
            <?php else: ?>
                <li class="breadcrumb-item"><?= Html::encode($indicatorModel->name) ?></li>
                <li class="breadcrumb-item active">update</li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="indicators-form">

        <?php $form = ActiveForm::begin(); ?>

        <div style="margin-bottom:10px;">
            <?= Html::a('<i class="fa fa-arrow-left"></i> Back', $back_url, ['class' => 'btn btn-default']) ?>
            <?= Html::resetButton('<i class="fa-solid fa-rotate-left"></i> Reset', ['class' => 'btn btn-default pull-right']) ?>
        </div>

        <?= $form->field($indicatorModel, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($indicatorModel, 'level')->dropDownList([ 'Work' => 'Work', 'Researcher' => 'Researcher', ], ['prompt' => '']) ?>
        <?= $form->field($indicatorModel, 'semantics')->dropDownList([ 'Impact' => 'Impact', 'Usage' => 'Usage', 'Productivity' => 'Productivity', 'Open Science' => 'Open Science', 'Career Stage' => 'Career Stage', ], ['prompt' => '']) ?>
        <?= $form->field($indicatorModel, 'intuition')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>
        <?= $form->field($indicatorModel, 'parameters')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>
        <?= $form->field($indicatorModel, 'calculation')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>
        <?= $form->field($indicatorModel, 'limitations')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>
        <?= $form->field($indicatorModel, 'availability')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>
        <?= $form->field($indicatorModel, 'code')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>
        <?= $form->field($indicatorModel, 'references')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>

        <div class="form-group">
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk"></i> Save', ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fa-solid fa-xmark"></i> Cancel', $back_url, ['class' => 'btn btn-danger']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>