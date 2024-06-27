<?php

use app\models\AssessmentFrameworks;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\AssessmentFrameworksSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Scholar Assessment';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="assessment-frameworks-index">

    <h1><?= Html::encode("Assessment Frameworks") ?></h1>

    <p>
        <?= Html::a('New Framework', ['create-framework'], ['class' => 'btn btn-custom-color']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $frameworkDataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            'name',
            'webpage',
            'description:html',
            [
                'class' => ActionColumn::className(),
                'header' => 'Actions',
                'urlCreator' => function ($action, AssessmentFrameworks $model, $key, $index, $column) {
                    $action .= "-framework";
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
