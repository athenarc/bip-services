<?php

use yii\helpers\Html;

$elem = $this->context;
$headingText = $elem->title ?? '';
$headingType = ! empty($elem->heading_type) ? $elem->heading_type : Yii::$app->params['defaultElementHeadingType'];
$topPadding = $elem->top_padding ?? '0px';
$bottomPadding = $elem->bottom_padding ?? '0px';
$showTopHr = $elem->show_top_hr ?? false;
$showBottomHr = $elem->show_bottom_hr ?? false;

// Build margin style
$marginStyle = '';

if (! empty($elem->margin_top)) {
    $marginStyle .= 'margin-top: ' . $elem->margin_top . '; ';
}

if (! empty($elem->margin_right)) {
    $marginStyle .= 'margin-right: ' . $elem->margin_right . '; ';
}

if (! empty($elem->margin_bottom)) {
    $marginStyle .= 'margin-bottom: ' . $elem->margin_bottom . '; ';
}

if (! empty($elem->margin_left)) {
    $marginStyle .= 'margin-left: ' . $elem->margin_left . '; ';
}

?>

<div id="divider_<?= $elem->element_id ?>" class="section-divider"<?= ! empty($marginStyle) ? ' style="' . trim($marginStyle) . '"' : '' ?>>
    
    <?php if ($topPadding): ?>
        <div style="padding-top: <?= $topPadding ?>;"></div>
    <?php endif; ?>

    <?php if ($showTopHr): ?>
        <hr>
    <?php endif; ?>

    <?php if (/*!$elem->edit_perm && */ $elem->show_description_tooltip): ?>
        <<?= $headingType ?>>
            <span role="button" data-toggle="popover" data-placement="auto" title="<?= $headingText ?>" data-content="<div><span class='green-bip'></span><?= (! empty($elem->description)) ? Html::encode($elem->description) : 'No description provided for this element.' ?></div>"> <?= $headingText ?> <small><i class="fa fa-info-circle light-grey-link" aria-hidden="true"></i></small></span>
        </<?= $headingType ?>>
    <?php else: ?>

        <?php if ($headingText): ?>
            <<?= $headingType ?>><?= $headingText ?></<?= $headingType ?>>
        <?php endif; ?>

        <?php if (isset($elem->description)): ?>
            <div style="text-align: justify; font-style: italic;">
                <?= $elem->description ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($showBottomHr): ?>
        <hr>
    <?php endif; ?>

    <?php if ($bottomPadding): ?>
        <div style="padding-bottom: <?= $bottomPadding ?>;"></div>
    <?php endif; ?>

</div>