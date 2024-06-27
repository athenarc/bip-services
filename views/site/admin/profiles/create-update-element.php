<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Elements $elementModel */
/** @var app\models\ElementIndicators $elementIndicatorsModel */
/** @var app\models\ElementIndicatorsForm $elementIndicatorsFormModel */
/** @var app\models\Indicators $indicatorList */
/** @var yii\widgets\ActiveForm $form */

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceAdminPanel.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

if ($elementModel->isNewRecord)
    $this->title = 'Create Element';
else
    $this->title = 'Update Element: ' . $elementModel->name;

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>

<script>
    $(document).ready(function(){

        $(".opt-checkbox").prop('disabled', true);

        var initialSelectedType = $("#elements-type").val();
        if (initialSelectedType != null) {
            initialSelectedType = initialSelectedType.replace(' ', '-');
            $(".element-type-section").hide();
            $("#element-type-" + initialSelectedType).show();
        }
        
        $("#elements-type").change(function(){
            var selectedType = $(this).val().replace(' ', '-');
            $(".element-type-section").hide();
            $("#element-type-" + selectedType).show();
        });

        $('#selectDeselectButton').click(function(){
            var checkboxes = $('input[type="checkbox"]');
            checkboxes.prop('checked', !checkboxes.prop('checked'));
        });

        var facetTypes = ['topics', 'roles', 'availability', 'work-type']
        
        facetTypes.forEach(function(facet) {

            var facetClass = "." + facet + '-checkbox';
            var facetOptClass = "." + facet + '-opt-checkbox';
            var checkboxOptsGroup = $(facetOptClass);

            if($(facetClass).is(':checked')){
                checkboxOptsGroup.prop('disabled', false);
            } else {
                checkboxOptsGroup.prop('disabled', true);
            }

            $(facetClass).change(function(){

                if($(this).is(':checked')){
                    checkboxOptsGroup.prop('disabled', false);
                } else {
                    checkboxOptsGroup.prop('disabled', true);
                    checkboxOptsGroup.prop('checked', false);
                }
            });
        });

    });
</script>

<div class="elements-update">

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
    
    <div class="elements-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($elementModel, 'template_id')->hiddenInput(['value' => $template_id])->label(false) ?>

        <?= $form->field($elementModel, 'type')->dropDownList([
            'Facets' => 'Facets',
            'Indicators' => 'Indicators',
            'Narrative' => 'Narrative',
            'Contributions List' => 'Contributions List', 
        ], [
            'prompt' => [
                'text' => 'Select Type of Element',
                'options' => ['disabled' => true, 'selected' => true]
            ],
            'id' => 'elements-type',
            'disabled' => ($elementModel->isNewRecord) ? false : true,
        ])->label(false); ?>
        
        <?= $form->field($elementModel, 'name')->textInput(['maxlength' => true]) ?>

        <div id="element-type-Facets" class="element-type-section" style="display: none;">
        <!-- <div id="element-type-Facets" class="element-type-section"> -->
            <h1><?= Html::encode('Facets') ?></h1>

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
        <!-- <div id="element-type-Indicators" class="element-type-section"> -->

            <div class="indicators-header" style="display: flex; align-items: center">
                <h1 style="margin: 0px"><?= Html::encode('Indicators') ?></h1>
                <?= Html::button('Select/Deselect All', ['class' => 'btn btn-xs', 'id' => 'selectDeselectButton', 'style' => 'margin-left: 10px; margin-bottom: 0px; bottom: 0;']) ?>
            </div>
            <!-- < ?php $indicatorLevels = ["Researcher", "Work"]; ?> -->
            <?php $indicatorLevels = ["Researcher"]; ?>

            <div class="flex-wrap" style="gap: 20px;">
                <?php foreach ($indicatorLevels as $ilevel): ?>
                    <div style="flex: 1;">
                        <!-- <h3 style="color: inherit;">< ?= $ilevel ?>-Level Indicators</h3> -->
                        <ul style="list-style-type: none; padding: 0; margin: 0;">
                            <?php
                            $lastSemantics = null;
                            foreach ($indicatorList as $indicator):
                                if ($indicator->level !== $ilevel) {
                                    continue;
                                }
                                $indicatorId = $indicator->id;
                                $indicatorName = $indicator->name;
                                $indicatorSemantics = $indicator->semantics;
                                $indicatorLevel = $indicator->level;
                                $isChecked = false;

                                if (!empty($existing_indicators)) {
                                    foreach ($existing_indicators as $existing_indicator) {
                                        if ($existing_indicator['id'] == $indicatorId) {
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

                            <li style="list-style-type: none; padding: 0; margin-bottom: -15px;">
                                <label for="selectedIndicators[]" style="margin: 0; padding: 0; font-weight: normal;">
                                    <?= $form->field($elementIndicatorsFormModel, 'selectedIndicators[]', ['errorOptions' => ['tag' => null]])->checkbox([
                                        'label' => $indicatorName,
                                        'labelOptions' => ['style' => 'font-weight: normal'],
                                        'value' => $indicatorId,
                                        'uncheck' => null,
                                        'template' => "{input}{label}",
                                        'checked' => $isChecked,
                                        'class' => 'green-checkbox',
                                    ])->label(false); ?>
                                </label>
                            </li>
                            <?php
                                $lastSemantics = $indicatorSemantics;
                                endforeach;
                            ?>
                        </ul>
                    </div>
                <?php endforeach ?>
                <?= $form->field($elementIndicatorsFormModel, 'selectedIndicators[]', ['errorOptions' => ['tag' => null]])->hiddenInput()->error()->label(false); ?>
            </div>
        </div>

        <div id="element-type-Narrative" class="element-type-section" style="display: none;">
        <!-- <div id="element-type-Narrative" class="element-type-section"> -->

            <div class="narrative-header" style="display: flex; align-items: center">
                <h1><?= Html::encode('Narrative') ?></h1>
            </div>

            <?php if ($elementModel->isNewRecord): ?>
                <?= $form->field($elementNarrativesFormModel, 'title')->textInput(['maxlength' => true]); ?>
                <?= $form->field($elementNarrativesFormModel, 'description')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']); ?>
                <?= $form->field($elementNarrativesFormModel, 'hide_when_empty')->checkbox([
                    'checked' => false,
                    'class' => ['green-checkbox', 'hide-when-empty-checkbox'],
                ])->label(false) ?>
            <?php else: ?>
                <?php if ($elementModel->type == "Narrative"): ?>
                    <?= $form->field($elementNarrativesFormModel, 'title')->textInput(['value' => $elementNarrativesModel->title, 'maxlength' => true]); ?>
                    <?= $form->field($elementNarrativesFormModel, 'description')->textarea(['value' => $elementNarrativesModel->description, 'rows' => 6, 'class' => 'rich_text_area_admin']); ?>

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
        <!-- <div id="element-type-Contributions-List" class="element-type-section" style="display: none;">
            <h4>Contribution List Logic</h4>
            <p>Custom logic for Contribution List...</p>
        </div> -->
        <div class="form-group" style="margin-top: 10px;">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-danger']) ?>
            <?= Html::a('Back', ['update-template', 'id' => $template_id, 'profile_template_category_id' => $profile_template_category_id], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>