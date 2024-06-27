<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Indicators $indicatorModel */

$this->title = $indicatorModel->name;
\yii\web\YiiAsset::register($this);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>
<div class="indicators-view">

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
        <?= Html::a('Update', ['update-indicator', 'id' => $indicatorModel->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete-indicator', 'id' => $indicatorModel->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Back', ['admin-indicators'], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $indicatorModel,
        'attributes' => [
            // 'id',
            'name',
            'level',
            'semantics',
            'intuition:html',
            'parameters:html',
            'calculation:html',
            'limitations:html',
            'availability:html',
            'code:html',
            'references:html',
        ],
    ]) ?>

</div>
