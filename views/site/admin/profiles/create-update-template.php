<?php

use Yii;
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
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var app\models\Templates $model */

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceAdminPanel.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

// Include jQuery UI for draggable functionality
$this->registerJsFile('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js', ['depends' => ['yii\web\JqueryAsset']]);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");

$back_url = ($templateModel->isNewRecord) 
    ? ['view-template-category', 'id' => $profile_template_category_id] 
    : ['view-template', 'id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id];

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
        <li class="<?= $section_indicators ? 'active' : ''?>">
        <a class="" <?= !$section_indicators ? "href=" . Url::to(['site/admin-indicators']) : "" ?>>Indicators</a>
        </li>
        <li class="<?= $section_profiles ? 'active' : ''?>">
        <a class="" <?= !$section_profiles ? "href=" . Url::to(['site/admin-profiles']) : "" ?>>Profile Templates</a>
        </li>
    </ul>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-admin">
            <li class="breadcrumb-item">...</li>
            <li class="breadcrumb-item">Template</li>
            <?php if ($templateModel->isNewRecord): ?>
                <li class="breadcrumb-item active">new</li>
            <?php else: ?>
                <li class="breadcrumb-item"><?= Html::encode($templateModel->name) ?></li>
                <li class="breadcrumb-item active">update</li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="templates-form">

        <?php $templateForm = ActiveForm::begin(); ?>

            <div style="margin-bottom:10px;">
                <?= Html::a('<i class="fa-solid fa-arrow-left"></i> Back', $back_url, ['class' => 'btn btn-default']) ?>
                <?= Html::resetButton('<i class="fa-solid fa-rotate-left"></i> Reset', ['class' => 'btn btn-default pull-right']) ?>
            </div>

            <?= $templateForm->field($templateModel, 'profile_template_category_id')->hiddenInput(['value' => $profile_template_category_id])->label(false)?>
            <?= $templateForm->field($templateModel, 'name')->textInput(['maxlength' => true]) ?>
            <?= $templateForm->field($templateModel, 'url_name')->textInput(['maxlength' => true]) ?>
            <?= $templateForm->field($templateModel, 'description')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>

            <?= $templateForm->field($templateModel, 'language')->widget(Select2::classname(), [
                'data' => Yii::$app->params['languages'],
                'language' => 'en', // Set Select2 interface language
                'options' => ['placeholder' => 'Select Language...'],
                'pluginOptions' => [
                    'allowClear' => false, // Hide clear selection
                ],
            ]); ?>
                     
            <?= $templateForm->field($templateModel, 'visible')->checkbox() ?>

            <!-- Hidden field to store elements data -->
            <?= Html::hiddenInput('elementsData', '', ['id' => 'elementsData']) ?>

            <?php if (!$templateModel->isNewRecord): ?>
                
                <h2><?= Html::encode('Elements') ?>
               
                <?php if (!$templateModel->isNewRecord): ?>
                    <?= Html::a('<i class="fa-solid fa-plus"></i> Add Element', ['create-element', 'template_id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-success pull-right']) ?>
                <?php endif ?>
            
            </h2>

                <?= $elementsDataProvider->setSort([
                    'defaultOrder' => ['order' => SORT_ASC]
                ]); ?>
                
                <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'id' => 'grid-view',
                    'dataProvider' => $elementsDataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'name',
                        'type',
                        [
                            'class' => ActionColumn::className(),
                            'header' => 'Actions',
                            'buttons' => [
                                'view' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-eye"></i> View', 
                                        $url, 
                                        ['title' => 'View', 'class' => 'btn btn-sm btn-default']
                                    );
                                },
                                'update' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-edit"></i> Edit', 
                                        $url, 
                                        ['title' => 'Edit', 'class' => 'btn btn-sm btn-primary']
                                    );
                                },
                                'delete' => function ($url, $model, $key) {
                                    return Html::a(
                                        '<i class="fas fa-trash"></i> Delete', 
                                        $url, 
                                        [
                                            'title' => 'Delete', 
                                            'class' => 'btn btn-sm btn-danger', 
                                            'data-confirm' => 'Are you sure you want to delete this item?',
                                            'data-method' => 'post'
                                        ]
                                    );
                                },
                            ],
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
                <?= Html::submitButton('<i class="fa-solid fa-floppy-disk"></i> Save', ['class' => 'btn btn-success']) ?>
                <?= Html::a('<i class="fa-solid fa-xmark"></i> Cancel', $back_url, ['class' => 'btn btn-danger']) ?>
            </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>