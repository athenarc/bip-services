<?php

/*
 *  Impact icons view!
 *
 * (First Version: July 2021)
 *
 */
use yii\helpers\Url;

?>

<?php if ($has_scores_classes): ?>

    <?php if (isset($options["header"])): ?>
        <div class="col-md-12 col-xs-12">
            <div class="details-scores well">
                <span class="legend green-bip"><?= $options['header'] ?></span>
    <?php endif; ?>

                <div class='col-xs-3 text-center'>
                    <?php if (!isset($options["header"])): ?>
                        <div class='row pyramid-header'>
                            <span title="Popularity">Popularity <i class="fa fa-question-circle" aria-hidden="true"></i></span>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <span role="button" class="impact-icon <?php if (isset($options["css_classes"])) echo $options['css_classes']; ?> popularity-icon impact-icon-<?= $popularity_class ?>" data-toggle="popover" data-placement="auto" data-hover-title = "<?= $popularity_class_message_short ?>" title="<b>Popularity (<?= $popularity_class_perc ?>)</b>" data-content="<?= $popularity_popover_content ?>"> <i class="fa fa-fire" aria-hidden="true"></i> </span>
                    </div>
                </div>
                <div class='col-xs-3 text-center'>
                    <?php if (!isset($options["header"])): ?>
                        <div class='row pyramid-header'>
                            <span title="Influence">Influence <i class="fa fa-question-circle" aria-hidden="true"></i></span>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <span role="button" class="impact-icon <?php if (isset($options["css_classes"])) echo $options['css_classes']; ?> influence-icon impact-icon-<?= $influence_class ?>" data-toggle="popover" data-placement="auto" data-hover-title = "<?= $influence_class_message_short ?>" title="<b>Influence (<?= $influence_class_perc ?>)</b>" data-content="<?= $influence_popover_content ?>"> <i class="fa fa-university" aria-hidden="true"></i> </span>
                    </div>
                </div>
                <div class='col-xs-3 text-center'>
                    <?php if (!isset($options["header"])): ?>
                        <div class='row pyramid-header'>
                            <span title="Citation Count">Citation Count<i class="fa fa-question-circle" aria-hidden="true"></i></span>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <span role="button" class="impact-icon <?php if (isset($options["css_classes"])) echo $options['css_classes']; ?> cc-icon impact-icon-<?= $cc_class ?>" data-toggle="popover" data-placement="auto" data-hover-title = "<?= $cc_class_message_short ?>" title="<b>Citation Count (<?= $cc_class_perc ?>)</b>" data-content="<?= $cc_popover_content ?>"> <i class="fa-solid fa-quote-left" aria-hidden="true"></i> </span>
                    </div>
                </div>
                <div class='col-xs-3 text-center'>
                    <?php if (!isset($options["header"])): ?>
                        <div class='row pyramid-header'>
                            <span title="Impulse">Impulse <i class="fa fa-question-circle" aria-hidden="true"></i></span>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <span role="button" class="impact-icon <?php if (isset($options["css_classes"])) echo $options['css_classes']; ?> impulse-icon impact-icon-<?= $impulse_class ?>" data-toggle="popover" data-placement="auto" data-hover-title = "<?= $impulse_class_message_short ?>" title="<b>Impulse (<?= $impulse_class_perc ?>)</b>" data-content="<?= $impulse_popover_content ?>"> <i class="fa fa-rocket" aria-hidden="true"></i> </span>
                    </div>
                </div>
    <?php if (isset($options["header"])): ?>
            </div>
        </div>
    <?php endif; ?>

<?php endif; ?>