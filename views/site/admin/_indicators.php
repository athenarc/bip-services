<?php

use app\models\Indicators;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Indicators';

?>
<div class="indicators-index">

    <nav aria-label="breadcrumb" style="position: relative;">
        <ol class="breadcrumb breadcrumb-admin mb-0">
            <li class="breadcrumb-item"><?= Html::a('Indicators', Url::to(['site/admin-indicators'])) ?></li>
        </ol>

        <?= Html::a('<i class="fa-solid fa-plus"></i> New', ['create-indicator'], ['class' => 'btn btn-success', 'style' => 'position: absolute; right: 0; top: 50%; transform: translateY(-50%);']) ?>
    </nav>

    <p>
        
    </p>

    <?= GridView::widget([
        'dataProvider' => $indicatorDataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'level',
            'semantics',
            'intuition:html',
            [
                'class' => ActionColumn::className(),
                'header' => 'Actions',
                'urlCreator' => function ($action, Indicators $model, $key, $index, $column) {
                    $action .= "-indicator";
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
