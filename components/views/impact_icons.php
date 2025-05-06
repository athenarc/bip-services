<?php

/*
 *  Impact icons view!
 *
 * (First Version: July 2021, Fixed May 2025)
 *
 */
?>

<?php if ($has_scores_classes): ?>
    <div style="margin: 6px 0 6px 6px; display: flex; gap: 8px; flex-wrap: nowrap; align-items: center;">

        <!-- Impact header + icons + scores -->
        <div style="display: flex; align-items: center; gap: 6px;">
            <div style="font-weight: bold; font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;">
                Impact:
            </div>

            <div style="display: flex; align-items: center; gap: 4px;">
                <span role="button" class="impact-icon popularity-icon impact-icon-<?= $popularity_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $popularity_class_message_short ?>" title="<b>Popularity (<?= $popularity_class_perc ?>)</b>" data-content="<?= $popularity_popover_content ?>">
                    <i class="fa fa-fire" aria-hidden="true"></i>
                </span>
                <?php if (!empty($showScoreLabel)): ?>
                    <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;"><?= isset($popularity_score) ? $popularity_score : '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon influence-icon impact-icon-<?= $influence_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $influence_class_message_short ?>" title="<b>Influence (<?= $influence_class_perc ?>)</b>" data-content="<?= $influence_popover_content ?>">
                    <i class="fa fa-university" aria-hidden="true"></i>
                </span>
                <?php if (!empty($showScoreLabel)): ?>
                    <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;"><?= isset($influence_score) ? $influence_score : '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon cc-icon impact-icon-<?= $cc_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $cc_class_message_short ?>" title="<b>Citation Count (<?= $cc_class_perc ?>)</b>" data-content="<?= $cc_popover_content ?>">
                    <i class="fa fa-quote-left" aria-hidden="true"></i>
                </span>
                <?php if (!empty($showScoreLabel)): ?>
                    <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;"><?= isset($cc_score) ? $cc_score : '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon impulse-icon impact-icon-<?= $impulse_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $impulse_class_message_short ?>" title="<b>Impulse (<?= $impulse_class_perc ?>)</b>" data-content="<?= $impulse_popover_content ?>">
                    <i class="fa fa-rocket" aria-hidden="true"></i>
                </span>
                <?php if (!empty($showScoreLabel)): ?>
                    <span style="font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;"><?= isset($impulse_score) ? $impulse_score : '-' ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Attention section on the same line -->
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-weight: bold; font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080;">/ Attention:</span>
            <span title="Num. of user bookmarks" style="display: flex; align-items: center; gap: 3px; color: #808080;">
                <i class="fa fa-bookmark" aria-hidden="true"></i> <span style="color: #808080;"><?= isset($num_likes) ? $num_likes : '0' ?></span>
            </span>
            <span title="Num. of unique page views" style="display: flex; align-items: center; gap: 3px; color: #808080;">
                <i class="fa fa-eye" aria-hidden="true"></i> <span style="color: #808080;"><?= isset($num_views) ? $num_views : '0' ?></span>
            </span>
        </div>
    </div>
<?php else: ?>
    <span class="grey-text" title="Impact scores non applicable"><i class="fa fa-minus"></i></span>
<?php endif; ?>