<?php

$item = $this->context;
$topic_id = (string) ($item->concept['id'] ?? '');

?>


<div>
    <span class='green-bip'>Description:</span>
    <?= empty(($item->concept['description'])) ? 'N/A' : ucfirst(str_replace('"', "'", $item->concept['description'])) . '.' ?>
</div>
<div>
    <a target='_blank' class='green-bip' href='<?= $item->concept['wikidata'] ?>'> Link to Wikidata <i class='fa fa-external-link-square' aria-hidden='true'></i></a>
</div>
<div>
    <span class='green-bip'>Confidence:</span>
    <?= empty(($item->concept['concept_score'])) ? 'N/A' : round($item->concept['concept_score'], 2)?>
</div>
<?php if (! empty($item->paper_id) && $topic_id !== '' && ! empty($item->can_report_topic)): ?>
    <div class="text-right" style="margin-top: 8px;">
        <?php $is_reported = ! empty($item->concept['reported_irrelevant']); ?>
        <button
            type="button"
            class="btn btn-default btn-xs grey-link report-topic-btn"
            data-paper-id="<?= (int) $item->paper_id ?>"
            data-topic-id="<?= \yii\helpers\Html::encode($topic_id) ?>"
            data-list-id="<?= (int) ($item->list_id ?? 0) ?>"
            data-owner-user-id="<?= (int) ($item->profile_owner_user_id ?? 0) ?>"
            data-topic-name="<?= \yii\helpers\Html::encode($item->concept['display_name'] ?? '') ?>"
            title="<?= $is_reported ? 'Undo this topic report for this research product.' : 'Report this topic as irrelevant for this research product.' ?>"
            data-reported="<?= $is_reported ? '1' : '0' ?>">
            <i class="fa fa-flag"></i> <?= $is_reported ? 'Undo report' : 'Report' ?>
        </button>
    </div>
<?php endif; ?>