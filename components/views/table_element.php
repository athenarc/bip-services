<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use kartik\builder\TabularForm;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use app\components\common\CommonUtils;
$this->registerJsFile('@web/js/tableElement.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$elem = $this->context;
$headingType = !empty($elem->heading_type) ? $elem->heading_type : Yii::$app->params['defaultElementHeadingType'];

?>

<div>

    <?php if (!$elem->edit_perm): ?>

        <?php if (!empty($elem->table_data) || !$elem->hide_when_empty): ?>
            <<?= $headingType ?>>
                <span role="button" data-toggle="popover" data-placement="auto" title="<?= $elem->title ?>" data-content="<div><span class='green-bip'></span><?= (!empty($elem->description)) ? Html::encode($elem->description) : "No description provided for this element." ?></div>"> <?= $elem->title ?> <small><i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></small></span>
            </<?= $headingType ?>>
        <?php endif; ?>

        <?php if (!empty($elem->table_data)): ?>
            <div class="panel panel-default table-responsive dynamic-table-panel">
                <table class="table table-hover table-bordered dynamic-table">
                    <thead style = "background: linear-gradient(to bottom, #fff 0%, #eee 100%);">
                        <tr>
                            <?php foreach ($elem->table_headers as $header => $header_width): ?>
                                <th width = "<?= isset($header_width) ? $header_width . '%' : '' ?>"><?= $header ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($elem->table_data as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><textarea class="form-control search-box element-table-input" readonly><?= $cell ?></textarea></td>
                                <?php endforeach;?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?php if (!$elem->hide_when_empty): ?>
                <div class="alert alert-warning text-center" role="alert">
                    Information for this element is not currently provided by the researcher.
                </div>
            <?php endif ?>
        <?php endif; ?>


    <?php else: ?>

        <<?= $headingType ?>>
            <?= $elem->title ?>
        </<?= $headingType ?>>

        <div style="text-align: justify; font-style: italic;">
            <?= $elem->description ?>
        </div>
        <?php if (!empty($elem->max_rows)): ?>
            <p class="text-warning">        
                Note that a maximum of <?= $elem->max_rows ?> rows is allowed in this table.
            </p>
        <?php endif; ?>

        <div class="panel panel-default table-responsive dynamic-table-panel">

            <table class="table table-hover dynamic-table" data-element-id="<?= $elem->element_id?>" data-max-rows="<?= $elem->max_rows?>">
                <thead style = "background: linear-gradient(to bottom, #fff 0%, #eee 100%);">
                    <tr>
                        <?php $header_action_width = 8;
                        foreach ($elem->table_headers as $header => $header_width): ?>
                            <th width = "<?= isset($header_width) ? $header_width*(100-$header_action_width)/100 . '%' : '' ?>"><?= $header ?></th>
                        <?php endforeach; ?>
                        <th style="width: <?=  $header_action_width ?>%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($elem->table_data)): ?>
                        <?php foreach ($elem->table_data as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><textarea class="form-control search-box element-table-input"><?= $cell ?></textarea></td>
                                <?php endforeach;?>
                                <td style="vertical-align: middle"><button class="btn btn-danger remove-table-row"><i class="glyphicon glyphicon-minus"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-top: 1px solid #ddd;">
                <span class="status-bar">
                    <span class="status-message"><?= CommonUtils::timeSinceUpdate($elem->last_updated) ?></span>
                </span>
                
                <button type="button" class="btn btn-custom-color add-table-row">
                    <i class="fas fa-plus"></i> Add
                </button> 
            </div>

        </div>

    <?php endif; ?>

</div>