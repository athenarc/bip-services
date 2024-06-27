<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use app\models\ElementNarratives;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $indicatorDataProvider */
/** @var app\models\Elements $elementModel */

$this->title = $elementModel->name;
// $this->params['breadcrumbs'][] = ['label' => 'Elements', 'url' => ['index']];
// $this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>
<div class="elements-view">

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
        <?= Html::a('Update', ['update-element', 'id' => $elementModel->id, 'template_id' => $elementModel->template_id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete-element', 'id' => $elementModel->id, 'template_id' => $elementModel->template_id, 'profile_template_category_id' => $profile_template_category_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('Back', ['view-template', 'id' => $elementModel->template_id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-default']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $elementModel,
        'attributes' => [
            // 'id',
            // 'template_id',
            'name',
            'type',
            // 'order',
        ],
    ]) ?>
    
    <?php if ($elementModel->type == "Facets"): ?>
        <div id="element-type-Facets" class="facets-list">
            <h1><?= Html::encode('Facets') ?></h1>

            <?php $facetTypes = ["Topics", "Roles", "Availability", "Work type"]; ?>
            
            <div class="flex-wrap" style="gap: 20px;">
                <?php foreach ($facetTypes as $fType): ?>
                    <div style="flex: 1;">
                    <?php
                        $isCheckedFacet = false;

                        if ($fType != "Availability" && $fType != "Work type") {
                            $optionValues = ["visualize_opt", "numbers_opt"];
                            $optionLabels = ["Visualization Button", "Numbers"];
                        }
                        else {
                            $optionValues = ["numbers_opt"];
                            $optionLabels = ["Numbers"];
                        }
                        
                        if (!empty($selected_facets)) {
                            foreach ($selected_facets as $sel_facet) {
                                if ($sel_facet['type'] == $fType){
                                    $isCheckedFacet = true;
                                }
                            }
                        }
                    ?>
                    <label for="selectedFacets[]" style="display: block; font-size: 24px; font-weight: bold;">
                        <input type="checkbox" class="green-checkbox view-only-checkbox disabled-checkbox <?= str_replace(' ', '-', strtolower($fType)) . '-checkbox' ?>" name="selectedFacets[]" value="<?= $fType ?>" <?= $isCheckedFacet ? 'checked' : '' ?> onclick="return false;">
                        <?= $fType ?>
                    </label>
                    <?php $isCheckedOpts = false; ?>
                    <?php $i = 0; ?>
                    <?php foreach ($optionValues as $opt): ?>
                        <?php $isCheckedOpts = false; ?>
                        <ul style="list-style-type: none; padding: 0; margin: 0;" id="facetOptionsGroup">
                            <?php
                                if (!empty($selected_facets)) {
                                    foreach ($selected_facets as $sel_facet) {
                                        if ($sel_facet['type'] == $fType && $sel_facet[$opt] == true) {
                                            $isCheckedOpts = true;
                                        }
                                    }
                                }
                            ?>
                            <li>
                                <label for="selectedFacets[]" style="margin: 0px; font-weight: normal; font-size: 14px;">
                                    <input type="checkbox" class="green-checkbox view-only-checkbox disabled-checkbox <?= str_replace(' ', '-', strtolower($fType)) . '-opt-checkbox' ?>" name="selectedFacets[]" value="<?= $fType . "-" . $opt ?>" <?= $isCheckedOpts ? 'checked' : '' ?> onclick="return false;">
                                    <?= $optionLabels[$i] ?>
                                </label>
                            </li>
                        </ul>
                        <?php $i++; ?>
                    <?php endforeach ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($elementModel->type == "Indicators"): ?>
        <div id="element-type-Indicators" class="indicators-list">

            <h1 style="margin:0px"><?= Html::encode('Indicators') ?></h1>

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
                <?php endforeach; ?>
            </div>
        </div>
    <?php elseif ($elementModel->type == "Narrative"): ?>
        <h1><?= Html::encode('Narrative') ?></h1>

        <?= DetailView::widget([
            'model' => $elementNarrativesModel,
            'attributes' => [
                'title',
                'description:html',
                'hide_when_empty:boolean'
            ],
        ]) ?>
    <?php endif; ?>
</div>
