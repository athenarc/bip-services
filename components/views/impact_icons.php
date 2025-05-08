<?php
/*
 *  Impact icons view!
 *
 * (First Version: July 2021, Fixed May 2025)
 */
?>

<?php
$mode = isset($options['mode']) ? $options['mode'] : 'default';
$showScoreLabel = isset($showScoreLabel) ? $showScoreLabel : false;
?>

<?php if ($has_scores_classes): ?>
    <div style="margin: 6px 0 6px 6px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">

        <!-- Impact section -->
        <div style="display: flex; align-items: center; gap: 6px;">
            <?php if ($mode !== 'compact'&& $showScoreLabel): ?>
                <div style="font-weight: bold; font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;">Impact:</div>
            <?php endif; ?>

            <div style="display: flex; align-items: center; gap: 8px;">
                <span role="button" class="impact-icon popularity-icon impact-icon-<?= $popularity_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $popularity_class_message_short ?>" title="<b>Popularity (<?= $popularity_class_perc ?>)</b>" data-content="<?= $popularity_popover_content ?>">
                    <i class="fa fa-fire" aria-hidden="true"></i>
                </span>
                <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                    <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;"><?= $popularity_score ?? '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon influence-icon impact-icon-<?= $influence_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $influence_class_message_short ?>" title="<b>Influence (<?= $influence_class_perc ?>)</b>" data-content="<?= $influence_popover_content ?>">
                    <i class="fa fa-university" aria-hidden="true"></i>
                </span>
                <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                    <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;"><?= $influence_score ?? '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon cc-icon impact-icon-<?= $cc_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $cc_class_message_short ?>" title="<b>Citation Count (<?= $cc_class_perc ?>)</b>" data-content="<?= $cc_popover_content ?>">
                    <i class="fa fa-quote-left" aria-hidden="true"></i>
                </span>
                <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                    <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;"><?= $cc_score ?? '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon impulse-icon impact-icon-<?= $impulse_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $impulse_class_message_short ?>" title="<b>Impulse (<?= $impulse_class_perc ?>)</b>" data-content="<?= $impulse_popover_content ?>">
                    <i class="fa fa-rocket" aria-hidden="true"></i>
                </span>
                <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                    <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;"><?= $impulse_score ?? '-' ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Attention section -->
        <?php if ($mode !== 'compact'&& $showScoreLabel): ?>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-weight: bold; font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;">/ Attention:</span>
                <span title="Bookmarks" style="display: flex; align-items: center; gap: 3px; color: #808080;">
                    <i class="fa fa-bookmark"></i> <span><?= $num_likes ?? '0' ?></span>
                </span>
                <span title="Views" style="display: flex; align-items: center; gap: 3px; color: #808080;">
                    <i class="fa fa-eye"></i> <span><?= $num_views ?? '0' ?></span>
                </span>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <span class="grey-text" title="Impact scores non applicable"><i class="fa fa-minus"></i></span>
<?php endif; ?>