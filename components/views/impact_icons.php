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
    <div class="impact-icons-wrapper">

        <!-- Impact section -->
        <div class="impact-section">
            <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                <div class="impact-header">Impact:</div>
            <?php endif; ?>

            <div class="impact-icons">
                <span role="button" class="impact-icon popularity-icon impact-icon-<?= $popularity_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $popularity_class_message_short ?>" title="<b>Popularity (<?= $popularity_class_perc ?>)</b>" data-content="<?= $popularity_popover_content ?>">
                    <i class="fa fa-fire" aria-hidden="true"></i>
                </span>
                <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                    <span class="impact-score"><?= $popularity_score ?? '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon influence-icon impact-icon-<?= $influence_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $influence_class_message_short ?>" title="<b>Influence (<?= $influence_class_perc ?>)</b>" data-content="<?= $influence_popover_content ?>">
                    <i class="fa fa-university" aria-hidden="true"></i>
                </span>
                <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                    <span class="impact-score"><?= $influence_score ?? '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon cc-icon impact-icon-<?= $cc_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $cc_class_message_short ?>" title="<b>Citation Count (<?= $cc_class_perc ?>)</b>" data-content="<?= $cc_popover_content ?>">
                    <i class="fa fa-quote-left" aria-hidden="true"></i>
                </span>
                <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                    <span class="impact-score"><?= $cc_score ?? '-' ?></span>
                <?php endif; ?>

                <span role="button" class="impact-icon impulse-icon impact-icon-<?= $impulse_class ?>" data-toggle="popover" data-placement="auto" data-hover-title="<?= $impulse_class_message_short ?>" title="<b>Impulse (<?= $impulse_class_perc ?>)</b>" data-content="<?= $impulse_popover_content ?>">
                    <i class="fa fa-rocket" aria-hidden="true"></i>
                </span>
                <?php if ($mode !== 'compact' && $showScoreLabel): ?>
                    <span class="impact-score"><?= $impulse_score ?? '-' ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Attention section -->
        <?php if ($mode !== 'compact' && $showScoreLabel): ?>
            <div class="attention-section">
                <span class="attention-header">/ Attention:</span>
                <span title="Bookmarks" class="attention-icon">
                    <i class="fa fa-bookmark"></i> <span><?= $num_likes ?? '0' ?></span>
                </span>
                <span title="Views" class="attention-icon">
                    <i class="fa fa-eye"></i> <span><?= $num_views ?? '0' ?></span>
                </span>
            </div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <span class="grey-text" title="Impact scores non applicable"><i class="fa fa-minus"></i></span>
<?php endif; ?>
