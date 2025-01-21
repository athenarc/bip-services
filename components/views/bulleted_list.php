<?php 
    use yii\helpers\Html; 
    use yii\helpers\Url;
    use yii\web\View;
    use app\components\common\CommonUtils;

    $elem = $this->context; 
    $headingText = isset($elem->title) ? $elem->title : '';
    $headingType = !empty($elem->heading_type) ? $elem->heading_type : Yii::$app->params['defaultElementHeadingType'];
   
    // Include the JavaScript file
    $this->registerJsFile('@web/js/utils.js', ['depends' => [\yii\web\JqueryAsset::class], 'position' => View::POS_END]); // needed for { debounce }
    $this->registerJsFile('@web/js/components/bulleted_list.js', ['depends' => [\yii\web\JqueryAsset::class], 'position' => View::POS_END]);

?>

<div id="list_<?= $elem->element_id ?>" class="row bulleted-list" data-id="<?= $elem->element_id ?>" max-elements="<?= $elem->elements_number ?>" >
    <div class="col-xs-12">
        <?php if ($elem->edit_perm): ?>
            <<?= $headingType ?>>
                <?= Html::encode($elem->title) ?>
            </<?= $headingType ?>>

            <button id="new-item-btn" type="button" class="add-item btn btn-sm btn-custom-color pull-right">New item</button>

            <div style="text-align: justify; font-style: italic;">
                <?= $elem->description ?>
                <?php if (!empty($elem->elements_number) && $elem->edit_perm): ?>
                    <p class="text-warning">        
                        Note that a maximum of <?= $elem->elements_number ?> items is allowed in this list.
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <<?= $headingType ?>>
                <span role="button" data-toggle="popover" data-placement="auto" title="<?= $elem->title ?>" data-content="<div><span class='green-bip'></span><?= (!empty($elem->description)) ? Html::encode($elem->description) : "No description provided for this element." ?></div>"> <?= $elem->title ?> <small><i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></small></span>
            </<?= $headingType ?>>
        <?php endif; ?>

        <div class="list-elements">
            
            <div id="no-items-msg" class="alert alert-warning text-center" role="alert" >
                    Information for this element is not currently provided by the researcher.
            </div>
            
            <?php if ($elem->edit_perm): ?>
                    <div class="container-items">
                        <?php foreach ($elem->items as $index => $item): ?>
                            <div id="<?= $item->id ?>_item" data-id="<?= $item->id ?>" class="item row" data-index="<?= $index ?>" style="margin-bottom: 5px;" data-id="121312">
                                <div class="col-md-12">
                                    <div class="input-group">
                                        <input type="text" 
                                            id="<?= $item->id ?>_input"
                                            class="form-control item-value search-box"
                                            value="<?= Html::encode($item->value) ?>" />
                                        <span class="input-group-btn">
                                            <button class="btn btn-danger remove-item" type="button">
                                                <i class="glyphicon glyphicon-minus" title="Remove list item"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="status-bar">
                            <!-- Display Typing or Last Updated -->
                            <span class="status-message" 
                                data-toggle="tooltip" 
                                <?php if ($elem->last_updated && strtotime($elem->last_updated) !== false): ?>
                                    title="<?= Yii::$app->formatter->asDatetime($elem->last_updated, 'php:Y-m-d H:i:s') . ' ' . date_default_timezone_get() ?>"
                                <?php endif; ?>
                            >
                                <?php if (!empty($elem->items)): ?>
                                    <?= !empty($elem->last_updated) ? CommonUtils::timeSinceUpdate($elem->last_updated) : 'No updates yet' ?>
                                <?php endif; ?>
                            </span>
                        </div>
            <?php else: ?>
                <ul>
                    <?php foreach ($elem->items as $index => $item): ?>
                        <li id="<?= $item->id ?>" data-index="<?= $index ?>" class="item">
                            <?= $item->value ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
