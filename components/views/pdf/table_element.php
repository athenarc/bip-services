<?php

use yii\helpers\Html;

$elem = $this->context;
$headingType = $elem->heading_type ?? 'h3';

$tableHeaders = [];
if (!empty($elem->table_headers)) {
    $tableHeaders = $elem->table_headers;
}

$tableData = $elem->table_data ?? [];
$hasTableData = is_array($tableData) && count($tableData) > 0;

?>

<div class="table-element">
    <?php if ($hasTableData || !$elem->hide_when_empty): ?>
        <<?= $headingType ?> class="table-element-title">
            <?= Html::encode($elem->title) ?>
        </<?= $headingType ?>>
        <?php if (!empty($elem->description)): ?>
            <div class="table-element-description">
                <?= Html::encode($elem->description) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($hasTableData): ?>
        <table class="table-element-table">
            <thead>
                <tr>
                    <?php foreach ($tableHeaders as $header => $width): ?>
                        <?php $widthAttr = ($width !== null && $width !== '') ? ' style="width: ' . Html::encode($width) . '%;"' : ''; ?>
                        <th<?= $widthAttr ?>><?= Html::encode($header) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tableData as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?= nl2br(Html::encode($cell)) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif (!$elem->hide_when_empty): ?>
        <div class="table-element-empty">
            The researcher has not yet provided input for this element.
        </div>
    <?php endif; ?>
</div>

