<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\AssessmentProtocols $protocolModel */
/** @var app\models\ProtocolIndicators $protocolIndicatorsModel */
/** @var app\models\ProtocolIndicatorsForm $protocolIndicatorsFormModel */
/** @var app\models\Indicators $indicatorList */
/** @var yii\widgets\ActiveForm $form */

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceAdminPanel.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");
?>
<script>
    $(document).ready(function(){
        $('#selectDeselectButton').click(function(){
            var checkboxes = $('input[type="checkbox"]');
            checkboxes.prop('checked', !checkboxes.prop('checked'));
        });
    });
</script>
<div class="assessment-protocols-create-update">
    
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

    <?php if ($protocolModel->isNewRecord): ?>
        <h1>Create Assessment Protocol</h1>
    <?php else: ?>
        <h1>Update Assessment Protocol: <?= $protocolModel->name ?></h1>
        <?php $assessment_framework_id = $protocolModel->assessment_framework_id ?>
    <?php endif ?>

    <div class="assessment-protocols-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($protocolModel, 'assessment_framework_id')->hiddenInput(['value' => $assessment_framework_id])->label(false)?>
        <?= $form->field($protocolModel, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($protocolModel, 'scope')->textarea(['rows' => 6, 'class' => 'rich_text_area_admin']) ?>

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

                        <li style="list-style-type: none; padding: 0; margin: 0;">
                            <label for="selectedIndicators[]" style="margin: 0; padding: 0; font-weight: normal;">
                                <?= $form->field($protocolIndicatorsFormModel, 'selectedIndicators[]', ['errorOptions' => ['tag' => null]])->checkbox([
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
        </div>

        <div class="form-group" style="margin-top: 15px">
            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-danger']) ?>
            <?= Html::a('Back', ['view-protocol', 'id' => $protocolModel->id, 'assessment_framework_id' => $protocolModel->assessment_framework_id], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
