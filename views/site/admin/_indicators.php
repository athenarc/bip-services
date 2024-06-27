<?php

use app\models\AssessmentFrameworks;
use app\models\Indicators;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Indicators';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="indicators-index">

    <h1><?= Html::encode("Indicators") ?></h1>

    <p>
        <?= Html::a('New Indicator', ['create-indicator'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $indicatorDataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
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
