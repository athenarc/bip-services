<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\AssessmentProtocols $protocolModel */
/** @var yii\data\ActiveDataProvider $indicatorDataProvider */
/** @var app\models\IndicatorsSearch $searchModel */

$this->title = $protocolModel->name;
\yii\web\YiiAsset::register($this);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>
<div class="assessment-protocols-view">

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
        <?= Html::a('Update', ['update-protocol', 'id' => $protocolModel->id, 'assessment_framework_id' => $protocolModel->assessment_framework_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete-protocol', 'id' => $protocolModel->id, 'assessment_framework_id' => $protocolModel->assessment_framework_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Back', ['view-framework', 'id' => $protocolModel->assessment_framework_id], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $protocolModel,
        'attributes' => [
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
        ],
    ]) ?>

</div>

<div class="indicators-list">

    <h1 style="margin:0px"><?= Html::encode('Indicators') ?></h1>

    <?php
        if ($protocolModel->protocolIndicators) {
            foreach ($protocolModel->protocolIndicators as $protocol_indicator) {
                $indicator = $protocol_indicator->indicator;
                
                $selected_indicators[] = [
                    'id' => Html::encode($indicator->id)
                ];
            }
        }
        else {
            $selected_indicators = [];
        }
    ?>

    <!-- < ?php $indicatorLevels = ["Researcher", "Work"]; ?> -->
    <?php $indicatorLevels = ["Researcher"]; ?>

    <div class="flex-wrap" style="gap: 20px;">
        <?php foreach ($indicatorLevels as $ilevel): ?>
        <div style="flex: 1;">
            <!-- <h3 style="color: inherit">< ?= $ilevel ?>-Level Indicators</h3> -->
            <ul style="list-style-type: none; padding: 0; margin: 0;">
                <?php
                    $lastSemantics = null;
                    foreach ($all_indicators as $indicator):
                        if ($indicator->level !== $ilevel) {
                            continue;
                        }
                        $indicatorId = $indicator->id;
                        $indicatorName = $indicator->name;
                        $indicatorSemantics = $indicator->semantics;
                        $indicatorLevel = $indicator->level;
                        $isChecked = false;

                        if (!empty($selected_indicators)) {
                            foreach ($selected_indicators as $selected_indicator) {
                                if ($selected_indicator['id'] == $indicatorId) {
                                    $isChecked = true;
                                    break;
                                }
                            }
                        }

                        if ($indicatorSemantics !== $lastSemantics):
                ?>
                <li style="list-style-type: none; padding: 0; margin: 0;">
                    <h4><strong><?= $indicatorSemantics ?></strong></h4>
                </li>
                <?php endif; ?>

                <li style="list-style-type: none; padding: 0; margin: 0;">
                    <label style="font-weight: normal;">
                        <input type="checkbox" class="green-checkbox view-only-checkbox disabled-checkbox" name="selectedIndicators[]" value="<?= $indicatorId ?>" <?= $isChecked ? 'checked' : '' ?> onclick="return false;">
                        <?= $indicatorName ?>
                    </label>
                </li>
                <?php
                    $lastSemantics = $indicatorSemantics;
                endforeach;
                ?>
            </ul>
        </div>
        <?php endforeach ?>
    </div>
</div>