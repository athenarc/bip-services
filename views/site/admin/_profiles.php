<?php

use app\models\ProfileTemplateCategories;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ProfileTemplateCategoriesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Profile Template Categories';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="profiles-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('New Template Category', ['create-template-category'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $profilesDataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            'name',
            'description:html',
            [
                'class' => ActionColumn::className(),
                'header' => 'Actions',
                'urlCreator' => function ($action, ProfileTemplateCategories $model, $key, $index, $column) {
                    $action .= "-template-category";
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
