<?php

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
// $this->params['breadcrumbs'][] = ['label' => 'Templates', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
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

    <p>
        <?= Html::a('Update', ['update-template', 'id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete-template', 'id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Back', ['view-template-category', 'id' => $profile_template_category_id], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $templateModel,
        'attributes' => [
            // 'id',
            // 'profile_template_category_id',
            'name',
            'url_name',
            'scope:html',
        ],
    ]) ?>

</div>

<div class="elements-index">

    <h2>Elements</h2>

    <p>
        <?= Html::a('Add Element', ['create-element', 'template_id' => $templateModel->id, 'profile_template_category_id' => $profile_template_category_id,], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= $elementsDataProvider->setSort([
        'defaultOrder' => ['order' => SORT_ASC]
    ]); ?>

    <?= GridView::widget([
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
    ]); ?>


</div>