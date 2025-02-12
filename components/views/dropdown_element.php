<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\components\common\CommonUtils;
$this->registerJsFile('@web/js/dropdownElement.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$elem = $this->context;
$headingType = !empty($elem->heading_type) ? $elem->heading_type : Yii::$app->params['defaultElementHeadingType'];

?>

<div>

    <?php if (!$elem->edit_perm): ?>
        <?php if (!empty($elem->option_id) || !$elem->hide_when_empty): ?>
            <<?= $headingType ?>>
                <span role="button" data-toggle="popover" data-placement="auto" title="<?= $elem->title ?>" data-content="<div><span class='green-bip'></span><?= (!empty($elem->description)) ? Html::encode($elem->description) : "No description provided for this element." ?></div>"> <?= $elem->title ?> <small><i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></small></span>
            </<?= $headingType ?>>
        <?php endif; ?>

        <div>
            <?php if (!empty($elem->option_id)): ?>
                <?php if (array_key_exists($elem->option_id, $elem->elementDropdownOptionsArray)): ?>
                    <div style="text-align: justify">
                        <?= $elem->elementDropdownOptionsArray[$elem->option_id] ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: justify">
                        There is an error in the information provided by the researcher.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if (!$elem->hide_when_empty): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Information for this element is not currently provided by the researcher.
                    </div>
                <?php endif ?>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <<?= $headingType ?>>
            <?= $elem->title ?>
        </<?= $headingType ?>>

        <div style="text-align: justify; font-style: italic;">
            <?= $elem->description ?>
        </div>

        <div class="flex-wrap items-center">
            <div>
            <?= Html::dropDownList(
                "dropdown-instance_" . $elem->element_id,
                $elem->option_id,
                $elem->elementDropdownOptionsArray,
                [
                    'class' => 'form-control dropdown-options',
                    'prompt' => 'Select an Option',
                    'data-element-id' => $elem->element_id,
                ]
            ) ?>
            </div>
        </div>

    <?php endif; ?>

</div>