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
// $this->params['breadcrumbs'][] = ['label' => 'Profile Template Categories', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
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
        <?= Html::a('Update', ['update-template-category', 'id' => $templateCategoryModel->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete-template-category', 'id' => $templateCategoryModel->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Back', ['admin-profiles'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $templateCategoryModel,
        'attributes' => [
            // 'id',
            'name',
            'description:html',
        ],
    ]) ?>

</div>

<div class="templates-index">

   <h2>Templates</h2>

   <p>
       <?= Html::a('New Template', ['create-template', 'profile_template_category_id' => $templateCategoryModel->id], ['class' => 'btn btn-success']) ?>
   </p>

   <?= GridView::widget([
       'dataProvider' => $templateDataProvider,
       'columns' => [
           ['class' => 'yii\grid\SerialColumn'],
        //    'id',
        //    'profile_template_category_id', 
           'name',
           'scope:html',
           [
               'class' => ActionColumn::className(),
               'header' => 'Actions',
               'urlCreator' => function ($action, Templates $model, $key, $index, $column) {
                   $action .= "-template";
                   return Url::toRoute([$action, 'id' => $model->id, 'profile_template_category_id' => $model->profile_template_category_id]);
                }
           ],
        ],
    ]) ?>
 
</div>