<?php

/*
 *  Impact icons view!
 *
 * (First Version: July 2021)
 *
 */

?>

<?php if ($has_scores_classes): ?>

    <span <?= ! empty($options['show_score_label']) ? '' : 'class="space-nowrap"' ?>>

        <span role="button" class="impact-icon popularity-icon impact-icon-<?= $popularity_class ?>" data-toggle="popover" data-placement="auto" data-hover-title = "<?= $popularity_class_message_short ?>" title="<b>Popularity (<?= $popularity_class_perc ?>)</b>" data-content= "<?= $popularity_popover_content ?>"> <i class="fa fa-fire" aria-hidden="true"></i> </span>&nbsp;
        <?php if (! empty($options['show_score_label'])): ?>
            <span><?= $popularity_score ?? '-' ?></span>&nbsp;
        <?php endif; ?>

        <span role="button" class="impact-icon influence-icon impact-icon-<?= $influence_class ?>" data-toggle="popover" data-placement="auto" data-hover-title = "<?= $influence_class_message_short ?>" title="<b>Influence (<?= $influence_class_perc ?>)</b>" data-content="<?= $influence_popover_content ?>"> <i class="fa fa-university" aria-hidden="true"></i> </span>&nbsp;
        <?php if (! empty($options['show_score_label'])): ?>
            <span><?= $influence_score ?? '-' ?></span>&nbsp;
        <?php endif; ?>


        <span role="button" class="impact-icon cc-icon impact-icon-<?= $cc_class ?>" data-toggle="popover" data-placement="auto" data-hover-title = "<?= $cc_class_message_short ?>" title="<b>Citation Count (<?= $cc_class_perc ?>)</b>" data-content="<?= $cc_popover_content ?>"> <i class="fa-solid fa-quote-left" aria-hidden="true"></i> </span>&nbsp;
        <?php if (! empty($options['show_score_label'])): ?>
            <span><?= $cc_score ?? '-' ?></span>&nbsp;
        <?php endif; ?>

        <span role="button" class="impact-icon impulse-icon impact-icon-<?= $impulse_class ?>" data-toggle="popover" data-placement="auto" data-hover-title = "<?= $impulse_class_message_short ?>" title="<b>Impulse (<?= $impulse_class_perc ?>)</b>" data-content="<?= $impulse_popover_content ?>"> <i class="fa fa-rocket" aria-hidden="true"></i> </span>&nbsp;
        <?php if (! empty($options['show_score_label'])): ?>
            <span><?= $impulse_score ?? '-' ?></span>
        <?php endif; ?>

    </span>

<?php else: ?>

    <span class = "grey-text" title = "Impact scores non applicable"><i class="fa-solid fa-minus"></i></span>

<?php endif; ?>
