<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use app\models\ElementNarratives;
use yii\widgets\ListView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $indicatorDataProvider */
/** @var app\models\Elements $elementModel */

$this->title = $elementModel->name;

\yii\web\YiiAsset::register($this);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");

$heading_type_view = [
    'attribute' => 'heading_type',
    'value' => function($model) {
        return !empty($model->heading_type) ? $model->heading_type : Yii::$app->params['defaultElementHeadingType'] . ' (default)';
    }
];

?>
<div class="elements-view">

    <ul class="nav nav-tabs green-nav-tabs" style = "margin-bottom: 30px;">
        <li class="<?= $section_overview == "overview" ? 'active' : ''?>">
        <a class="" <?= !$section_overview ? "href=" . Url::to(['site/admin-overview']) : "" ?>>Overview</a>
        </li>
        <li class="<?= $section_spaces ? 'active' : ''?>">
        <a class="" <?= !$section_spaces ? "href=" . Url::to(['site/admin-spaces']) : "" ?>>Spaces</a>
        </li>
        <li class="<?= $section_indicators ? 'active' : ''?>">
        <a class="" <?= !$section_indicators ? "href=" . Url::to(['site/admin-indicators']) : "" ?>>Indicators</a>
        </li>
        <li class="<?= $section_profiles ? 'active' : ''?>">
        <a class="" <?= !$section_profiles ? "href=" . Url::to(['site/admin-profiles']) : "" ?>>Profile Templates</a>
        </li>
    </ul>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-admin">
            <li class="breadcrumb-item">...</li>
            <li class="breadcrumb-item">...</li>
            <li class="breadcrumb-item">Element</li>
            <li class="breadcrumb-item"><?= Html::encode($this->title) ?></li>
            <li class="breadcrumb-item active">view</li>
        </ol>
    </nav>

    <p>
        <?= Html::a('<i class="fa-solid fa-arrow-left"></i> Back', ['view-template', 'id' => $elementModel->template_id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-default']) ?>
        <?= Html::a('<i class="fa-solid fa-pen-to-square"></i> Update', ['update-element', 'id' => $elementModel->id, 'template_id' => $elementModel->template_id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="fa-solid fa-trash-can"></i> Delete', ['delete-element', 'id' => $elementModel->id, 'template_id' => $elementModel->template_id, 'profile_template_category_id' => $profile_template_category_id], [
            'class' => 'btn btn-danger pull-right',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
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
                                $isCheckedGreen = false;
                                $isCheckedGray = false;

                                if (!empty($selected_indicators)) {
                                    foreach ($selected_indicators as $selected_indicator) {
                                        if ($selected_indicator['id'] == $indicatorId) {
                                            if ($selected_indicator['status'] == 'Enabled') {
                                                $isCheckedGreen = true;
                                                break;
                                            }
                                            else if ($selected_indicator['status'] == 'Hidden') {
                                                $isCheckedGray = true;
                                                break;
                                            }
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
                                <input type="checkbox" class="green-checkbox view-only-checkbox disabled-checkbox" name="selectedIndicators[]" value="<?= $indicatorId ?>" <?= $isCheckedGreen ? 'checked' : '' ?> onclick="return false;">
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
                $heading_type_view,
                'description:html',
                [
                    'attribute' => 'limit_type',
                    'value' => function ($model) {
                        return ElementNarratives::getLimitTypeList()[$model->limit_type];
                    }
                ],
                'limit_value:integer',
                'hide_when_empty:boolean'
            ],
        ]) ?>
    <?php elseif ($elementModel->type == "Section Divider"): ?>
        <h1><?= Html::encode('Section Divider') ?></h1>

        <?= DetailView::widget([
            'model' => $elementDividerModel,
            'attributes' => [
                'title',
                $heading_type_view,
                'description:html',
                'show_description_tooltip:boolean',
                'top_padding:integer',
                'bottom_padding:integer',
                'show_top_hr:boolean',
                'show_bottom_hr:boolean',
            ],
        ]) ?>
    <?php elseif ($elementModel->type == "Contributions List"): ?>
        <h1><?= Html::encode('Contributions List') ?></h1>

        <?= DetailView::widget([
            'model' => $elementContributionsModel,
            'attributes' => [
                'show_header:boolean',
                $heading_type_view,
                'sort',
                'top_k:integer',
                'show_pagination:boolean',
                'page_size:integer',
            ],
        ]) ?>
    <?php elseif ($elementModel->type == "Bulleted List"): ?>
        <h1><?= Html::encode('Bulleted List') ?></h1>

        <?= DetailView::widget([
            'model' => $elementBulletedListModel,
            'attributes' => [
                'title',
                $heading_type_view,
                'description:html',
                'elements_number:integer',
            ],
        ]) ?>
    <?php elseif ($elementModel->type == "Dropdown"): ?>
        <h1><?= Html::encode('Dropdown') ?></h1>
        <?php 
            echo DetailView::widget([
                'model' => $elementDropdownModel, // Use the main model for the widget
                'attributes' => [
                    'title',
                    $heading_type_view,
                    'description:html',
                    'hide_when_empty:boolean',
                    [
                        'label' => 'Dropdown Options',
                        'value' => function ($model) use ($elementDropdownModel) {
                            return implode('<br>', array_map(function ($optionModel) {
                                return $optionModel->option_name;
                            }, $elementDropdownModel->elementDropdownOptions));
                        },
                        'format' => 'html', // Enables HTML rendering
                    ],
                ],
            ]);
        ?>
    <?php elseif ($elementModel->type == "Table"): ?>
        <h1><?= Html::encode('Table') ?></h1>
        <?php 
            echo DetailView::widget([
                'model' => $elementTableModel, // Use the main model for the widget
                'attributes' => [
                    'title',
                    $heading_type_view,
                    'description:html',
                    'hide_when_empty:boolean',
                    [
                        'label' => 'Table Headers',
                        'value' => function ($model) use ($elementTableModel) {
                            return implode('<br>', array_map(function ($headerModel) {
                                return $headerModel->header_name . (isset($headerModel->header_width) ? " ({$headerModel->header_width}%)" : '');
                            }, $elementTableModel->elementTableHeaders));
                        },
                        'format' => 'html', // Enables HTML rendering
                    ],
                ],
            ]);
        ?>
    <?php endif; ?>
</div>
