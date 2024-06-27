<?php 

use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $elem = $this->context; ?>

<div>

    <?php if (!$elem->edit_perm): ?>
        <h3>    
            <?php if (!$elem->hide_when_empty): ?>
                <span role="button" data-toggle="popover" data-placement="auto" title="<?= $elem->title ?>" data-content="<div><span class='green-bip'></span><?= (!empty($elem->description)) ? Html::encode($elem->description) : "No description provided for this narrative." ?></div>"> <?= $elem->title ?> <small><i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></small></span>
            <?php endif ?>
        </h3>
    <?php else: ?>
        <h3><?= $elem->title ?></h3>
    <?php endif; ?>

    <?php if (!$elem->edit_perm): ?>
        
        <div>
            <?php if (!empty($elem->value)): ?>
                <div style="text-align: justify">
                    <?= $elem->value ?>
                </div>
            <?php else: ?>
                <?php if (!$elem->hide_when_empty): ?>
                    <div class="alert alert-warning" role="alert">
                        <strong>Holy BIP!</strong> Information for this narrative is not currently provided by the user.
                    </div>
                <?php endif ?>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <div style="text-align: justify; font-style: italic;">
            <?= $elem->description ?>
        </div>

        <?= Html::textarea("narrative_instances[$elem->index][value]", $elem->value, ['class' => 'form-control narrative-element-textarea', 'rows' => 6, 'placeholder' => "Please provide your input here", "ajax_link" => Url::to(['scholar/save-narrative-instance']), "element_id" => $elem->element_id]) ?>
        <div id="status_message_<?= $elem->element_id ?>" class="status-message">&nbsp;</div>
    <?php endif; ?>

</div>