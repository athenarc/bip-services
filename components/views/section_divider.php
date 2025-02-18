<?php
$elem = $this->context;
$headingText = isset($elem->title) ? $elem->title : '';
$headingType = !empty($elem->heading_type) ? $elem->heading_type : Yii::$app->params['defaultElementHeadingType'];
$topPadding = isset($elem->top_padding) ? $elem->top_padding : '0px';
$bottomPadding = isset($elem->bottom_padding) ? $elem->bottom_padding : '0px';
$showTopHr = isset($elem->show_top_hr) ? $elem->show_top_hr : false;
$showBottomHr = isset($elem->show_bottom_hr) ? $elem->show_bottom_hr : false;
?>

<div id="divider_<?= $elem->element_id ?>" class="section-divider">
    
    <?php if ($topPadding): ?>
        <div style="padding-top: <?= $topPadding ?>;"></div>
    <?php endif; ?>

    <?php if ($showTopHr): ?>
        <hr>
    <?php endif; ?>

    <?php if ($headingText): ?>
        <<?= $headingType ?>><?= $headingText ?></<?= $headingType ?>>
    <?php endif; ?>

    <?php if (isset($elem->description)): ?>
        <?= $elem->description ?>
    <?php endif; ?>

    <?php if ($showBottomHr): ?>
        <hr>
    <?php endif; ?>

    <?php if ($bottomPadding): ?>
        <div style="padding-bottom: <?= $bottomPadding ?>;"></div>
    <?php endif; ?>

</div>