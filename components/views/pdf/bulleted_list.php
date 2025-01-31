<?php 
    use yii\helpers\Html; 

    $elem = $this->context; 

?>

<div id="list_<?= $elem->element_id ?>" class="bulleted-list" data-id="<?= $elem->element_id ?>" max-elements="<?= $elem->elements_number ?>" >
    <div>
        <h3>
            <span 
                role="button" data-toggle="popover" data-placement="auto" title="<?= $elem->title ?>"
                data-content="<div><span class='green-bip'></span><?= (!empty($elem->description)) ? Html::encode($elem->description) : "No description provided for this element." ?></div>"><?= $elem->title ?>
            </span>
        </h3>

        <div class="list-elements">
            <ul>
                <?php foreach ($elem->items as $index => $item): ?>
                    <li id="<?= $item->id ?>" data-index="<?= $index ?>" class="item">
                        <?= $item->value ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
