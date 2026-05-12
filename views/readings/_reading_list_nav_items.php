<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $reading_lists */
/** @var array $saved_reading_list_ids */
/** @var array $reading_list_owner_labels keyed by owner user_id */
/** @var \app\models\ReadingList|null $current_reading_list */
/** @var bool $show_drag_handle */
$reading_list_owner_labels = $reading_list_owner_labels ?? [];
$saved_reading_list_ids = $saved_reading_list_ids ?? [];

foreach ($reading_lists as $reading_list) {
    $list_id = $reading_list->id;
    $list_title = $reading_list->title;
    $is_orphan = ! empty($reading_list->is_orphan_saved);
    $is_own_list = ! $is_orphan && (int) $reading_list->user_id === (int) Yii::$app->user->id;
    $is_saved_list = in_array((int) $list_id, $saved_reading_list_ids ?? [], true);
    $list_description = trim((string) ($reading_list->description ?? ''));
    $list_description_title = $list_description !== ''
        ? $list_description
        : 'No description is provided for this reading list.'; ?>
    <li class="toc-item reading-list-item-row<?= $is_orphan ? ' reading-list-nav-orphan' : '' ?> <?= (! $is_orphan && isset($current_reading_list) && $list_id == $current_reading_list->id) ? 'is-active' : '' ?>" data-list-id="<?= (int) $list_id ?>">
        <?php if ($is_orphan): ?>
            <span class="toc-link reading-list-item-inline reading-list-nav-orphan-title">
                <span class="reading-list-item-title"><?= Html::encode($list_title) ?></span>
            </span>
        <?php else: ?>
            <a class="toc-link reading-list-item-inline <?= (isset($current_reading_list) && $list_id == $current_reading_list->id) ? 'green-bip' : '' ?>" href="<?= Url::to(['readings/list/' . $list_id]) ?>">
                <?php if (! empty($show_drag_handle)): ?>
                    <span class="light-grey-link reading-list-drag-handle" title="Drag to reorder">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </span>
                <?php endif; ?>
                <span class="reading-list-item-title"><?= Html::encode($list_title) ?></span>
            </a>
        <?php endif; ?>
        <span class="reading-list-item-actions">
            <?php if (! $is_orphan && ! $is_own_list && $is_saved_list): ?>
                <?php
                    $owner_uid = (int) $reading_list->user_id;
    $owner_username = $reading_list_owner_labels[$owner_uid] ?? '';
    $owner_hover = $owner_username !== ''
                        ? ('List owner: ' . $owner_username)
                        : 'List from another user'; ?>
                <span class="light-grey-link"
                      title="<?= Html::encode($owner_hover) ?>"
                      aria-label="<?= Html::encode($owner_hover) ?>">
                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                </span>
            <?php endif; ?>
            <?php if (! $is_orphan): ?>
                <span role="button" class="light-grey-link" data-toggle="popover" data-placement="auto" title="<b><?= Html::encode($list_title) ?></b>" data-content="<div><span class='green-bip'></span> <?= Html::encode($list_description_title) ?></div>">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </span>
            <?php endif; ?>
            <?php if ((! $is_own_list && $is_saved_list) || $is_orphan): ?>
                <form method="POST" action="<?= Url::to(['readings/remove-saved-reading-list']) ?>" class="reading-inline-form">
                    <input type="hidden" name="reading_list_id" value="<?= (int) $list_id ?>">
                    <button type="submit"
                            class="light-grey-link reading-inline-action-btn"
                            title="Remove from saved lists"
                            onclick="return confirm('Remove this list from your saved lists?');">
                        <i class="fa-solid fa-trash fa-xs" aria-hidden="true"></i>
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($is_own_list): ?>
                <a href="<?= Url::to(['readings/delete-reading-list/', 'selected_list_id' => $list_id]) ?>"
                   class="light-grey-link"
                   title="Delete reading list"
                   onclick="return confirm('Are you sure you want to delete this reading list?');">
                    <i class="fa-solid fa-trash fa-xs" aria-hidden="true"></i>
                </a>
            <?php endif; ?>
        </span>
    </li>
    <?php
}
