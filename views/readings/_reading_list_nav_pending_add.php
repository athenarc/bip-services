<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var \app\models\ReadingList $current_reading_list */
/** @var string|null $current_reading_list_owner_label */
$list_id = (int) $current_reading_list->id;
$list_title = $current_reading_list->title;
$list_description = trim((string) $current_reading_list->description);
$list_description_title = $list_description !== ''
    ? $list_description
    : 'No description is provided for this reading list.';

$owner_label = $current_reading_list_owner_label ?? '';
$owner_hover = $owner_label !== ''
    ? ('List owner: ' . $owner_label)
    : 'List from another user';
?>
<hr class="reading-lists-nav-pending-divider" aria-hidden="true">
<ul class="reading-lists-nav-ul">
    <li class="toc-item reading-list-item-row is-active" data-list-id="<?= $list_id ?>">
        <a class="toc-link reading-list-item-inline green-bip" href="<?= Url::to(['readings/list/' . $list_id]) ?>">
            <span class="reading-list-item-title"><?= Html::encode($list_title) ?></span>
        </a>
        <span class="reading-list-item-actions">
            <span class="light-grey-link"
                  title="<?= Html::encode($owner_hover) ?>"
                  aria-label="<?= Html::encode($owner_hover) ?>">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
            </span>
            <span role="button" class="light-grey-link" data-toggle="popover" data-placement="auto" title="<b><?= Html::encode($list_title) ?></b>" data-content="<div><span class='green-bip'></span> <?= Html::encode($list_description_title) ?></div>">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
            </span>
            <form method="POST" action="<?= Url::to(['readings/save-shared-reading-list']) ?>" class="reading-inline-form">
                <input type="hidden" name="reading_list_id" value="<?= $list_id ?>">
                <button type="submit"
                        class="light-grey-link reading-inline-action-btn"
                        title="Add to saved">
                    <i class="fa-solid fa-plus fa-xs" aria-hidden="true"></i>
                </button>
            </form>
        </span>
    </li>
</ul>
