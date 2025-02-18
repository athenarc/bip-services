<?php

use yii\helpers\Html;

?>

<?php $elem = $this->context; ?>

<div>
    <h3>
        <span role="button" title="<?= $elem->title ?>" data-content="<div><span class='green-bip'></span><?= (!empty($elem->description)) ? Html::encode($elem->description) : "No description provided for this element." ?></div>"> <?= $elem->title ?></span>
    </h3>

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
                <div class="alert alert-warning" role="alert">
                    The researcher has not yet provided input for this element. 
                </div>
            <?php endif ?>
        <?php endif; ?>
    </div>
</div>