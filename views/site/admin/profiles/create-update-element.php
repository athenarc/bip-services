<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\helpers\Url;
use yii\jui\Accordion;
use app\models\ElementNarratives;
use wbraganca\dynamicform\DynamicFormWidget;

/** @var yii\web\View $this */
/** @var app\models\Elements $elementModel */
/** @var app\models\ElementIndicators $elementIndicatorsModel */
/** @var app\models\ElementIndicatorsForm $elementIndicatorsFormModel */
/** @var app\models\Indicators $indicatorList */
/** @var yii\widgets\ActiveForm $form */

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceAdminPanel.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/create_update_element.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerCssFile("@web/css/create-update-element.css", ['depends' => [\yii\bootstrap\BootstrapAsset::className()]]);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");

?>

<div class="elements-update">

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
            <?php if ($elementModel->isNewRecord): ?>
                <li class="breadcrumb-item">new</li> 
            <?php else: ?>
                <li class="breadcrumb-item"><?= Html::encode($elementModel->name) ?></li>
                <li class="breadcrumb-item active">update</li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="elements-form">

        <?php $form = ActiveForm::begin([
            'options' => [
                'enctype' => 'multipart/form-data',
                'id' => 'element-form'
            ]
        ]);?>
        
        <div style="margin-bottom:10px;">
            <?= Html::a('<i class="fa-solid fa-arrow-left"></i> Back', ['view-template', 'id' => $template_id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-default']) ?>
            <?= Html::resetButton('<i class="fa-solid fa-rotate-left"></i> Reset', ['class' => 'btn btn-default pull-right']) ?>
        </div>
        
        <?= $form->field($elementModel, 'template_id')->hiddenInput(['value' => $template_id])->label(false) ?>

        <!-- please keep this list alphabetically ordered by name -->
        <?= $form->field($elementModel, 'type')->dropDownList([
            'Bulleted List' => 'Bulleted List',
            'Contributions List' => 'Contributions List',
            'Dropdown' => 'Dropdown',
            'Facets' => 'Facets',
            'Indicators' => 'Indicators',
            'Narrative' => 'Narrative',
            'Section Divider' => 'Section Divider',
        ], [
            'prompt' => [
                'text' => 'Select element type',
                'options' => ['disabled' => true, 'selected' => true]
            ],
            'id' => 'elements-type',
            'disabled' => ($elementModel->isNewRecord) ? false : true,
        ])->label(false); ?>
        
        <?= $form->field($elementModel, 'name')->textInput(['maxlength' => true]) ?>

        <div id="element-type-Bulleted-List" class="element-type-section" style="display: none;">
            <div class="divider-header" style="display: flex; align-items: center">
                <h1><?= Html::encode('Bulleted List') ?></h1>
            </div>

        <?php if ($elementModel->isNewRecord || $elementModel->type == "Bulleted List"): ?>
            <?= $form->field($elementBulletedListModel, 'title')->textInput(['maxlength' => true]); ?>
            <?= $form->field($elementBulletedListModel, 'heading_type')->dropDownList([
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ], ['prompt' => 'Select header size']) ?>
            <?= $form->field($elementBulletedListModel, 'description')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']); ?>
            <?= $form->field($elementBulletedListModel, 'elements_number')->textInput([
                'type' => 'number',
                'class' => 'search-box form-control',
                'min' => 1,
                'step' => 1,
                'placeholder' => 'Enter a positive integer'
            ])->hint('Leave empty to allow for dynamic additions and removals.') ?>

        <?php endif; ?>

        </div>

        <div id="element-type-Facets" class="element-type-section" style="display: none;">

            <div class="facets-header" style="display: flex; align-items: center">
                <h1><?= Html::encode('Facets') ?></h1>
            </div>

            <?php $facetTypes = ["Topics", "Roles", "Availability", "Work type"]; ?>

            <div class="flex-wrap" style="gap: 20px;">
                <?php foreach ($facetTypes as $fType): ?>
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

                        if (!empty($existing_facets)) {
                            foreach ($existing_facets as $ex_facet) {
                                if ($ex_facet['type'] == $fType){
                                    $isCheckedFacet = true;
                                }
                            }
                        }
                    ?>

                    <div style="flex: 1;">
                        <label for="selectedFacets[]" style="margin: 0; padding: 0; font-weight: normal;">
                            <?= $form->field($elementFacetsFormModel, 'selectedFacets[]', ['errorOptions' => ['tag' => null]])->checkbox([
                                'label' => $fType,
                                'labelOptions' => ['style' => 'display: block; font-size: 24px; font-weight: bold;'],
                                'value' => $fType,
                                'uncheck' => null,
                                'template' => "{label}{input}",
                                'checked' => $isCheckedFacet,
                                'class' => ['green-checkbox', str_replace(' ', '-', strtolower($fType)) . '-checkbox'],
                            ])->label(false); ?>
                        </label>

                        <?php $isCheckedOpts = false; ?>
                        <?php $i = 0; ?>
                        <?php foreach ($optionValues as $opt): ?>

                        <ul style="list-style-type: none; padding: 0; margin-bottom: -15px;" id="facetOptionsGroup">
                            <?php $isCheckedOpts = false; ?>

                            <?php
                                if (!empty($existing_facets)) {
                                    foreach ($existing_facets as $ex_facet) {
                                        if ($ex_facet['type'] == $fType && $ex_facet[$opt] == true) {
                                            $isCheckedOpts = true;
                                        }
                                    }
                                }
                            ?>

                            <li style="list-style-type: none; padding: 0; margin: 0;">
                                <?= $form->field($elementFacetsFormModel, 'selectedFacets[]', ['errorOptions' => ['tag' => null]])->checkbox([
                                    'label' => 'Show ' . $optionLabels[$i],
                                    'labelOptions' => ['style' => 'margin: 0px; font-weight: normal; font-size: 14px;'],
                                    'value' => $fType . "-" . $opt,
                                    'uncheck' => null,
                                    'template' => "{label}{input}",
                                    'checked' => $isCheckedOpts,
                                    // 'disabled' => true,
                                    'class' => ['green-checkbox', str_replace(' ', '-', strtolower($fType)) . '-opt-checkbox', 'opt-checkbox'],
                                ])->label(false); ?>
                            </li>
                        </ul>
                        <?php $i++; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="element-type-Indicators" class="element-type-section" style="display: none;">

            <div class="indicators-header" style="display: flex; align-items: center">
                <h1><?= Html::encode('Indicators') ?></h1>
                <button type="button" class="toggle-all-indicators">Collapse All</button>
                <select class="global-status-combobox">
                    <option value="" disabled selected>Group Actions</option>
                    <option value="Enabled">Enable All Groups</option>
                    <option value="Disabled">Disable All Groups</option>
                    <option value="Hidden">Hide All Groups</option>
                </select>
            </div>

            <?php
                $groupedIndicators = [];
                foreach ($indicatorList as $indicator):
                    if ($indicator->level !== "Researcher") {
                        continue;
                    }
                    $groupedIndicators[$indicator->semantics][] = ['id' => $indicator->id, 'name' => $indicator->name];
                endforeach;

                // Default semantics order for new records
                $defaultSemanticsOrder = ['Impact', 'Productivity', 'Open Science', 'Career Stage'];

                if (!$elementModel->isNewRecord) {
                    if (!empty($existing_indicators)) {
                        $semanticsOrderCombined = [];
                        foreach ($existing_indicators as $existing_indicator) {
                            $semanticsOrderCombined[] = [
                                'semantics_value' => $existing_indicator['semantics'], 
                                'semantics_number' => $existing_indicator['semantics_order']
                            ];
                        }
            
                        // print_r($semanticsOrder);
                        
                        // Sort the combined array by the 'number' key
                        usort($semanticsOrderCombined, function($a, $b) {
                            return $a['semantics_number'] <=> $b['semantics_number'];
                        });
                        
                        // Extract the sorted values
                        $sortedValues = array_column($semanticsOrderCombined, 'semantics_value');
                        $semanticsOrder = array_values(array_unique($sortedValues));
                    }

                    $indicatorOrderBySemantics = [];
                    foreach ($existing_indicators as $existing_indicator) {
                        $indicatorOrderBySemantics[$existing_indicator['semantics']][] = [
                            'indicator_id' => $existing_indicator['id'], 
                            'indicator_order' => $existing_indicator['indicator_order']
                        ];
                    }

                    foreach ($indicatorOrderBySemantics as $semantics => $indicators) {
                        usort($indicators, function($a, $b) {
                            return $a['indicator_order'] <=> $b['indicator_order'];
                        });
                        $indicatorOrderBySemantics[$semantics] = $indicators;
                    }
                } else {
                    $semanticsOrder = $defaultSemanticsOrder;

                    $indicatorOrderBySemantics = [];
                    foreach ($groupedIndicators as $semantics => $indicators) {
                        foreach ($indicators as $key => $indicator) {
                            $indicatorOrderBySemantics[$semantics][] = [
                                'indicator_id' => $indicator['id'],
                                'indicator_order' => $key
                            ];
                        }
                    }
                }

                // Sort the grouped indicators by the provided order
                $sortedGroupedIndicators = [];
                foreach ($semanticsOrder as $semantics) {
                    if (isset($groupedIndicators[$semantics])) {
                        $sortedGroupedIndicators[$semantics] = [];

                        // Sort indicators by the provided order from DB
                        if (isset($indicatorOrderBySemantics[$semantics])) {
                            foreach ($indicatorOrderBySemantics[$semantics] as $orderData) {
                                foreach ($groupedIndicators[$semantics] as $indicator) {
                                    if ($indicator['id'] == $orderData['indicator_id']) {
                                        $sortedGroupedIndicators[$semantics][] = $indicator;
                                    }
                                }
                            }
                        }
                    }
                }
            ?>

            <div id="semantics-container">
                <?= $form->field($elementIndicatorsFormModel, 'semanticsOrder')->hiddenInput(['id' => 'semantics-order'])->label(false) ?>
                <?= $form->field($elementIndicatorsFormModel, 'indicatorOrder')->hiddenInput(['id' => 'indicator-order'])->label(false) ?>
                <?php foreach ($sortedGroupedIndicators as $semantics => $indicators): ?>
                    <div class="semantics-group" id="semantics-<?= strtolower(str_replace(' ', '-', $semantics)) ?>">
                        <div class="semantics-heading"  style="display: flex; align-items: center">
                            <h4><strong><?= Html::encode($semantics) ?></strong></h4>
                            <button type="button" class="toggle-indicators">Collapse</button>
                            <select class="group-status-combobox">
                                <option value="" disabled selected>Group Actions</option>
                                <option value="Enabled">Enable All</option>
                                <option value="Disabled">Disable All</option>
                                <option value="Hidden">Hide All</option>
                            </select>
                        </div>
                        <div class="indicator-container">
                            <?php foreach ($indicators as $indicator): ?>
                            <?php
                                $currentStatus = 'Enabled';
                                if (!empty($existing_indicators)) {
                                    foreach ($existing_indicators as $existing_indicator) {
                                        if ($existing_indicator['id'] == $indicator['id']) {
                                            $currentStatus = $existing_indicator['status'];
                                            break;
                                        }
                                    }
                                }

                                $dropdown = $form->field($elementIndicatorsFormModel, "selectedIndicators[{$indicator['id']}]", ['options' => ['class' => 'form-group-inline'], 'template' => "{label}\n{input}\n{error}", 'errorOptions' => ['tag' => null]])
                                    ->dropDownList(
                                        ['Enabled' => 'Enabled', 'Disabled' => 'Disabled', 'Hidden' => 'Hidden'],
                                        [
                                            'options' => [$currentStatus => ['Selected' => true]],
                                            'class' => 'form-control form-control-indicator-selection',
                                        ]
                                    )->label($indicator['name'], ['class' => 'control-label-inline indicator-heading']);

                                ?>
                                <div class="indicator-item" id="indicator-<?= $indicator['id'] ?>">
                                    <?= $dropdown ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <div id="element-type-Narrative" class="element-type-section" style="display: none;">

            <div class="narrative-header" style="display: flex; align-items: center">
                <h1><?= Html::encode('Narrative') ?></h1>
            </div>

            <?php if ($elementModel->isNewRecord): ?>
                <?= $form->field($elementNarrativesFormModel, 'title')->textInput(['maxlength' => true]); ?>
                <?= $form->field($elementNarrativesFormModel, 'heading_type')->dropDownList([
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ], ['prompt' => 'Select header size']) ?>
                <?= $form->field($elementNarrativesFormModel, 'description')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']); ?>
                <?= $form->field($elementNarrativesFormModel, 'limit_type')->dropDownList(ElementNarratives::getLimitTypeList())->hint('Choose to limit the text of this element by words or characters.') ?>
                <?= $form->field($elementNarrativesFormModel, 'limit_value')->textInput()->hint('Number of allowed words or characters for this text. Use 0 for no limit.') ?>
                <?= $form->field($elementNarrativesFormModel, 'hide_when_empty')->checkbox([
                    'checked' => false,
                    'class' => ['green-checkbox', 'hide-when-empty-checkbox'],
                ])->label(false) ?>
            <?php else: ?>
                <?php if ($elementModel->type == "Narrative"): ?>
                    <?= $form->field($elementNarrativesFormModel, 'title')->textInput(['value' => $elementNarrativesModel->title, 'maxlength' => true]); ?>
                    <?= $form->field($elementNarrativesFormModel, 'heading_type')->dropDownList(['h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6'], ['prompt' => 'Select header size', 'value' => $elementNarrativesModel->heading_type]) ?>
                    <?= $form->field($elementNarrativesFormModel, 'description')->textarea(['value' => $elementNarrativesModel->description, 'rows' => 6, 'class' => 'rich_text_area_admin']); ?>
                    <?= $form->field($elementNarrativesFormModel, 'limit_type')->dropDownList($elementNarrativesModel::getLimitTypeList(), ['value' => $elementNarrativesModel->limit_type]) ?>
                    <?= $form->field($elementNarrativesFormModel, 'limit_value')->textInput(['value' => $elementNarrativesModel->limit_value]) ?>

                    <?php 
                        $isCheckedHideNarrative = false;
                        if ($elementNarrativesModel->hide_when_empty) {
                            $isCheckedHideNarrative = true;
                        }
                    ?>
                    
                    <?= $form->field($elementNarrativesFormModel, 'hide_when_empty')->checkbox([
                        'checked' => $isCheckedHideNarrative,
                        'class' => ['green-checkbox', 'hide-when-empty-checkbox'],
                    ])->label(false) ?>
                <?php endif ?>
            <?php endif ?>

        </div>
        <div id="element-type-Section-Divider" class="element-type-section" style="display: none;">
            <div class="divider-header" style="display: flex; align-items: center">
                <h1><?= Html::encode('Section Divider') ?></h1>
            </div>

            <?php if ($elementModel->isNewRecord): ?>
                <?= $form->field($elementDividersFormModel, 'title')->textInput(['maxlength' => true]); ?>
                <?= $form->field($elementDividersFormModel, 'heading_type')->dropDownList([
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ], ['prompt' => 'Select header size']) ?>

                <h3>Padding</h3>
                <?= $form->field($elementDividersFormModel, 'top_padding')->textInput(['maxlength' => true, 'placeholder' => 'e.g., 20px']) ?>
                <?= $form->field($elementDividersFormModel, 'bottom_padding')->textInput(['maxlength' => true, 'placeholder' => 'e.g., 20px']) ?>

                <h3>Horizontal rule</h3>
                <?= $form->field($elementDividersFormModel, 'show_top_hr')->checkbox() ?>
                <?= $form->field($elementDividersFormModel, 'show_bottom_hr')->checkbox() ?>

            <?php else: ?>
                <?php if ($elementModel->type == "Section Divider"): ?>
                    <?= $form->field($elementDividersFormModel, 'title')->textInput(['value' => $elementDividersModel->title, 'maxlength' => true]); ?>
                    <?= $form->field($elementDividersFormModel, 'heading_type')->dropDownList([
                        'h1' => 'H1',
                        'h2' => 'H2',
                        'h3' => 'H3',
                        'h4' => 'H4',
                        'h5' => 'H5',
                        'h6' => 'H6',
                    ], [
                        'prompt' => 'Select header size',
                        'value' => $elementDividersModel->heading_type, // Pre-select saved value
                    ]) ?>

                    <h3>Padding</h3>
                    <?= $form->field($elementDividersFormModel, 'top_padding')->textInput(['value' => $elementDividersModel->top_padding, 'maxlength' => true, 'placeholder' => 'e.g., 20px']) ?>

                    <?= $form->field($elementDividersFormModel, 'bottom_padding')->textInput(['value' => $elementDividersModel->bottom_padding, 'maxlength' => true, 'placeholder' => 'e.g., 20px']) ?>

                    <h3>Horizontal rule</h3>

                    <?= $form->field($elementDividersFormModel, 'show_top_hr')->checkbox([
                        'checked' => $elementDividersModel->show_top_hr ? true : false,
                    ]) ?>

                    <?= $form->field($elementDividersFormModel, 'show_bottom_hr')->checkbox([
                        'checked' => $elementDividersModel->show_bottom_hr ? true : false,
                    ]) ?>
                <?php endif ?>
            <?php endif ?>
        </div>
        <?php if ($elementModel->isNewRecord || $elementModel->type == "Contributions List"): ?>
            <div id="element-type-Contributions-List" class="element-type-section" style="display: none;">
                <div class="divider-header" style="display: flex; align-items: center">
                    <h1><?= Html::encode('Contributions List') ?></h1>
                </div>
                <?= $form->field($elementContributionsModel, 'heading_type')->dropDownList([
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ], ['prompt' => 'Select header size']) ?>
                <?= $form->field($elementContributionsModel, 'sort')->dropDownList(array_combine(array_keys(Yii::$app->params['impact_fields']), array_keys(Yii::$app->params['impact_fields']))) ?>
                <?= $form->field($elementContributionsModel, 'top_k')->textInput([
                    'type' => 'number',
                    'class' => 'search-box form-control',
                    'min' => 1,
                    'step' => 1,
                    'placeholder' => 'Enter a positive integer'
                    ]) ?>

                <?= $form->field($elementContributionsModel, 'page_size')->textInput([
                    'type' => 'number',
                    'class' => 'search-box form-control',
                    'min' => 1,
                    'step' => 1,
                    'placeholder' => 'Enter a positive integer'
                ]) ?>

                <?= $form->field($elementContributionsModel, 'show_header')->checkbox(['class' => ['green-checkbox']]) ?>
                <?= $form->field($elementContributionsModel, 'show_pagination')->checkbox(['class' => ['green-checkbox']]) ?>
            </div>
        <?php endif ?>
        <?php if ($elementModel->isNewRecord || $elementModel->type == "Dropdown"): ?>
            <div id="element-type-Dropdown" class="element-type-section" style="display: none;">
                <div class="divider-header" style="display: flex; align-items: center">
                    <h1><?= Html::encode('Dropdown') ?></h1>
                </div>
                    <?= $form->field($elementDropdownModel, 'title')->textInput([
                        'class' => 'search-box form-control',
                    ]) ?>

                    <?= $form->field($elementDropdownModel, 'heading_type')->dropDownList([
                        'h1' => 'H1',
                        'h2' => 'H2',
                        'h3' => 'H3',
                        'h4' => 'H4',
                        'h5' => 'H5',
                        'h6' => 'H6',
                    ], ['prompt' => 'Select header size']) ?>
                                    
                    <?= $form->field($elementDropdownModel, 'description')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']); ?>

                    <?= $form->field($elementDropdownModel, 'hide_when_empty')->checkbox([
                        'class' => ['green-checkbox'],
                    ])->label(false) ?>
                    
                <?php 
                    DynamicFormWidget::begin([
                        'widgetContainer' => 'dynamicform_wrapper_dropdown_element', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                        'widgetBody' => '.container-items-dropdown-element', // required: css class selector
                        'widgetItem' => '.item-dropdown-element', // required: css class
                        // 'limit' => 4, // the maximum times, an element can be cloned (default 999)
                        'min' => 1, // 0 or 1 (default 1)
                        'insertButton' => '.add-item-dropdown-element', // css class
                        'deleteButton' => '.remove-item-dropdown-element', // css class
                        'model' => $elementDropdownOptionsModels[0],
                        'formId' => 'element-form',
                        'formFields' => [
                            'option_name',
                        ],
                    ]); 
                    ?>
                    <div style = "margin-bottom:10px">
                        <label class="pull-left" style="font-size: inherit;" >Dropdown Options</label>
                        <div class="pull-right">
                            <button type="button" class="add-item-dropdown-element btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="container-items-dropdown-element"><!-- widgetContainer -->
                        <?php foreach ($elementDropdownOptionsModels as $i => $elementDropdownOptionsModel): ?>
                            <div class="item-dropdown-element panel panel-default"><!-- widgetBody -->
                                <div class="panel-heading panel-heading-unset">
                                    <div class="pull-right">
                                        <button type="button" class="remove-item-dropdown-element btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="panel-body">
                                    <?php
                                        // necessary for update action.
                                        if (! $elementDropdownOptionsModel->isNewRecord) {
                                            echo Html::activeHiddenInput($elementDropdownOptionsModel, "[{$i}]id");
                                        }
                                    ?>
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <?= $form->field($elementDropdownOptionsModel, "[{$i}]option_name")->textInput(['maxlength' => true, 'class' => 'search-box form-control']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php DynamicFormWidget::end(); ?>
            </div>
        <?php endif ?>

        <div class="form-group" style="margin-top: 10px;">
            <?= Html::submitButton('<i class="fa-solid fa-floppy-disk"></i> Save', ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fa-solid fa-xmark"></i> Cancel', ['view-template', 'id' => $template_id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-danger']) ?>
         </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
