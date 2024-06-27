<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\AssessmentProtocols;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\AssessmentFrameworks $frameworkModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\AssessmentProtocolsSearch $searchModel */

$this->title = $frameworkModel->name;
\yii\web\YiiAsset::register($this);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>
<div class="assessment-frameworks-view">

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
        <?= Html::a('Update', ['update-framework', 'id' => $frameworkModel->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete-framework', 'id' => $frameworkModel->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Back', ['admin-scholar'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $frameworkModel,
        'attributes' => [
            // 'id',
            'name',
            'webpage',
            'description:html',
        ],
    ]) ?>

</div>

<div class="assessment-protocols-index">

    <h2><?= Html::encode("Assessment Protocols") ?></h2>

    <p>
        <?= Html::a('New Protocol', ['create-protocol', 'id' => $frameworkModel->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $protocolDataProvider,
        // 'filterModel' => $searchProtocolModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'assessment_framework_id',
            'name',
            'scope:html',
            [
                'label' => 'Indicators',
                'value' => function ($model) {
                    return count($model->protocolIndicators);
                }
            ],
            [
                'class' => ActionColumn::className(),
                'header' => 'Actions',
                'urlCreator' => function ($action, AssessmentProtocols $model, $key, $index, $column) {
                    $action .= "-protocol";
                    return Url::toRoute([$action, 'id' => $model->id, 'assessment_framework_id' => $model->assessment_framework_id]);
                 }
            ],
        ],
    ]); ?>
</div>
