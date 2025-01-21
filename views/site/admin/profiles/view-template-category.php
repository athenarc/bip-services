<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Templates;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\TemplatesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\ProfileTemplateCategories $templateCategoryModel */

$this->title = $templateCategoryModel->name;

\yii\web\YiiAsset::register($this);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>
<div class="profile-template-categories-view">
    
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
            <li class="breadcrumb-item"><?= Html::a('Template categories', Url::to(['site/admin-profiles'])) ?></li>
            <li class="breadcrumb-item"><?= Html::encode($this->title) ?></li>
            <li class="breadcrumb-item active">view</li>
        </ol>
    </nav>

    <p>
        <?= Html::a('<i class="fa-solid fa-arrow-left"></i> Back', ['admin-profiles'], ['class' => 'btn btn-default']) ?>
        <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> Update', ['update-template-category', 'id' => $templateCategoryModel->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa-solid fa-trash-can"></i> Delete', ['delete-template-category', 'id' => $templateCategoryModel->id], [
            'class' => 'btn btn-danger pull-right',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $templateCategoryModel,
        'attributes' => [
            'name',
            'description:html',
            [
                'attribute' => 'visible',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->visible ? '<i class="fa-solid fa-eye" title="Shown"></i>' : '<i class="fa-solid fa-eye-slash" title="Hidden"></i>';
                },
            ],
        ],
    ]) ?>

</div>

<div class="templates-index">

   <h2>Templates <?= Html::a('<i class="fa-solid fa-plus"></i> New template', ['create-template', 'profile_template_category_id' => $templateCategoryModel->id], ['class' => 'btn btn-success pull-right']) ?></h2>

   <?= GridView::widget([
       'dataProvider' => $templateDataProvider,
       'options' => [
            'class' => 'admin-table grey-text'
        ],
       'columns' => [
           ['class' => 'yii\grid\SerialColumn'],           
           [
                'attribute' => 'name',
                'format' => 'raw', // Ensures HTML output is not escaped
                'value' => function ($model) {
                    return Html::a(
                        $model->name . ' <i class="fa-solid fa-arrow-right-to-bracket"></i>',
                        ['site/view-template', 'id' => $model->id, 'profile_template_category_id' => $model->profile_template_category_id]
                    );
                },
            ],
           'description:html',
           [
                'attribute' => 'visible',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->visible ? '<i class="fa-solid fa-eye" title="Shown"></i>' : '<i class="fa-solid fa-eye-slash" title="Hidden"></i>';
                },
            ],
        ],
    ]) ?>
 
</div>