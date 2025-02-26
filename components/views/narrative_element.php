<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\components\common\CommonUtils;

$this->registerJsFile('@web/js/utils.js', ['depends' => [\yii\web\JqueryAsset::class], 'position' => View::POS_END]); // needed for { debounce }
$this->registerJsFile('@web/js/narrativeElement.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

?>

<?php $elem = $this->context; 
$headingText = isset($elem->title) ? $elem->title : '';
$headingType = !empty($elem->heading_type) ? $elem->heading_type : Yii::$app->params['defaultElementHeadingType'];

?>

<div>

    <?php if (!$elem->edit_perm): ?>
        <?php if (!$elem->hide_when_empty || !empty($elem->value)): ?>
            <<?= $headingType ?>>
                <span role="button" data-toggle="popover" data-placement="auto" title="<?= $elem->title ?>" data-content="<div><span class='green-bip'></span><?= (!empty($elem->description)) ? Html::encode($elem->description) : "No description provided for this element." ?></div>"><?= $elem->title ?> <small><i class="fa fa-info-circle light-grey-link" aria-hidden="true"></i></small></span>
            </<?= $headingType ?>>
        <?php endif ?>
    <?php else: ?>
        <<?= $headingType ?>><?= $elem->title ?> </<?= $headingType ?>>
    <?php endif; ?>

    <?php if (!$elem->edit_perm): ?>
        
        <div>
            <?php if (!empty($elem->value)): ?>
                <div style="text-align: justify">
                    <?= $elem->value ?>
                </div>
            <?php else: ?>
                <?php if (!$elem->hide_when_empty): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        The researcher has not yet provided input for this element. 
                    </div>
                <?php endif ?>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <div style="text-align: justify; font-style: italic;">
            <?= $elem->description ?>
        </div>

        <?= Html::textarea("narrative_instances[$elem->index][value]", $elem->value, [
            'class' => 'form-control narrative-element-textarea',
            'rows' => 6,
            'placeholder' => "Please provide your input here",
            "ajax_link" => Url::to(['scholar/save-narrative-instance']),
            "element_id" => $elem->element_id,
            "limit_type" => $elem->limit_type,
            "limit_value" => $elem->limit_value,
        ]) ?>

        <div id="status_message_<?= $elem->element_id ?>">
            <div class="status-bar">
            <span class="status-message" 
                data-toggle="tooltip" 
                <?php if ($elem->last_updated && strtotime($elem->last_updated) !== false): ?>
                    title="<?= Yii::$app->formatter->asDatetime($elem->last_updated, 'php:Y-m-d H:i:s') . ' ' . date_default_timezone_get() ?>"
                <?php endif; ?>
                ><?= CommonUtils::timeSinceUpdate($elem->last_updated) ?></span>
                
                <span class="status-count">
                    <?php  $displayStyle = $elem->messages['limit'] ? 'inline' : 'none';  ?>
                    <span class="glyphicon glyphicon-exclamation-sign limit-status" style="color: orange; display: <?= $displayStyle ?>;" title="<?= $elem->messages['limit'] ?>"></span>
                    <span class="count-message"><?= $elem->messages['count'] ?></span>
                </span>
            </div>
        </div>
    <?php endif; ?>

</div>