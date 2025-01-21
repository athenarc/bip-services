<?php

use Yii;
use app\models\Elements;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\Templates $model */
/** @var app\models\ElementsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = $templateModel->name;

\yii\web\YiiAsset::register($this);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");

?>

<div class="templates-view">
    
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
            <li class="breadcrumb-item">
                <?php if ($templateUrl): ?>
                    
                        <?= Html::a(Html::encode($this->title) . ' <i class="fa fa-external-link-square" aria-hidden="true"></i>', $templateUrl, [
                            'class' => 'main-green',
                            'target' => '_blank', // Open in a new tab
                        ]) ?>
                    
                <?php else:
                    echo Html::encode($this->title);
                endif; ?>
            </li>
            <li class="breadcrumb-item active">view</li>
        </ol>
    </nav>

    <p>
        <?= Html::a('<i class="fa-solid fa-arrow-left"></i> Back', ['view-template-category', 'id' => $profile_template_category_id], ['class' => 'btn btn-default']) ?>
        <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> Update', ['update-template', 'id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa-solid fa-trash-can"></i> Delete', ['delete-template', 'id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id], [
            'class' => 'btn btn-danger pull-right',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $templateModel,
        'attributes' => [
            'name',
            'url_name',
            'description:html',
            [
                'attribute' => 'language',
                'value' => function ($model) {
                    return Yii::$app->params['languages'][$model->language] ?? 'Unknown'; // Map code to full name
                }
            ],
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

<div class="elements-index">

    <h2>
        Elements
        <?= Html::a('<i class="fa-solid fa-plus"></i> New element', ['create-element', 'template_id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id,], ['class' => 'btn btn-success pull-right']) ?>
    </h2>

    <?= $elementsDataProvider->setSort([
        'defaultOrder' => ['order' => SORT_ASC]
    ]); ?>

    <?= GridView::widget([
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
    ]); ?>


</div>