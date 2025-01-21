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

?>
<div class="profiles-index">

    <nav aria-label="breadcrumb" style="position: relative;">
        <ol class="breadcrumb breadcrumb-admin mb-0">
            <li class="breadcrumb-item"><?= Html::a('Template categories', Url::to(['site/admin-profiles'])) ?></li>
        </ol>
        <?= Html::a('<i class="fa-solid fa-plus"></i> New', ['create-template-category'], ['class' => 'btn btn-success', 'style' => 'position: absolute; right: 0; top: 50%; transform: translateY(-50%);']) ?>
    </nav>

    <?= GridView::widget([
        'dataProvider' => $profilesDataProvider,
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
                        ['site/view-template-category', 'id' => $model->id]
                    );
                },
            ],
            'description:html',
            [
                'attribute' => 'visible',
                'format' => 'raw', // Ensures HTML output is not escaped
                'value' => function ($model) {
                    return $model->visible ? '<i class="fa-solid fa-eye" title="Shown"></i>' : '<i class="fa-solid fa-eye-slash" title="Hidden"></i>';
                },
            ]
        ],
    ]); ?>


</div>
