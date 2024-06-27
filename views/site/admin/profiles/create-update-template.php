<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\helpers\Url;
use kartik\sortable\Sortable;
use yii\bootstrap\Modal;
use app\models\Elements;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\Templates $model */

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceAdminPanel.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

// Include jQuery UI for draggable functionality
$this->registerJsFile('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js', ['depends' => ['yii\web\JqueryAsset']]);

$this->title = 'Update Template: ' . $templateModel->name;

if ($templateModel->isNewRecord)
    $this->title = 'Create Template';
else
    $this->title = 'Update Template: ' . $templateModel->name;

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>

<script>
    $(function() {
        $("#grid-view tbody").sortable({
            update: function(event, ui) {
                var newOrder = [];
                $("#grid-view tbody tr").each(function(index) {
                    newOrder.push($(this).data("key"));
                });

                // Send new order to server via AJAX
                $.post("update-order", { order: newOrder }, function(data) {
                    console.log("Order updated successfully");
                });
            }
        }).disableSelection();
    });
</script>
<style>
    .dragging {
        cursor: move;
    }
</style>
<div class="templates-create-update">

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

    <!-- <div class="title-header" style="display: flex; align-items: center"> -->
    <h1><?= Html::encode($this->title) ?></h1>
    <!-- </div> -->

    <div class="templates-form">

        <?php $templateForm = ActiveForm::begin(); ?>

            <?= $templateForm->field($templateModel, 'profile_template_category_id')->hiddenInput(['value' => $profile_template_category_id])->label(false)?>
            <?= $templateForm->field($templateModel, 'name')->textInput(['maxlength' => true]) ?>
            <?= $templateForm->field($templateModel, 'url_name')->textInput(['maxlength' => true]) ?>
            <?= $templateForm->field($templateModel, 'scope')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>
            
            <!-- Hidden field to store elements data -->
            <?= Html::hiddenInput('elementsData', '', ['id' => 'elementsData']) ?>

            <?php if (!$templateModel->isNewRecord): ?>
                
                <h2><?= Html::encode('Elements') ?></h2>

                <p>
                    <?php if (!$templateModel->isNewRecord): ?>
                        <?= Html::a('Add Element', ['create-element', 'template_id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-success']) ?>
                    <?php endif ?>
                </p>

                <?= $elementsDataProvider->setSort([
                    'defaultOrder' => ['order' => SORT_ASC]
                ]); ?>
                
                <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'id' => 'grid-view',
                    'dataProvider' => $elementsDataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        // 'id',
                        // 'template_id',
                        'name',
                        'type',
                        // 'order',
                        [
                            'class' => ActionColumn::className(),
                            'header' => 'Actions',
                            'urlCreator' => function ($action, Elements $model, $key, $index, $column) use ($profile_template_category_id) {
                                $action .= "-element";
                                return Url::toRoute([$action, 'id' => $model->id, 'template_id' => $model->template_id, 'profile_template_category_id' => $profile_template_category_id]);
                            }
                        ],
                    ],
                    'rowOptions' => function ($model, $key, $index, $grid) {
                        return ['class' => 'dragging'];
                    }
                ]); ?>
                <?php Pjax::end(); ?>
            <?php endif ?>

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                <?= Html::resetButton('Reset', ['class' => 'btn btn-danger']) ?>
                <?php if ($templateModel->isNewRecord): ?>
                    <?= Html::a('Back', ['view-template-category', 'id' => $profile_template_category_id], ['class' => 'btn btn-default']) ?>
                <?php else: ?>
                    <?= Html::a('Back', ['view-template', 'id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-default']) ?>
                <?php endif ?>
            </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>