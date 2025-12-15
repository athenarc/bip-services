<?php

use yii\helpers\Html;
use yii\helpers\Url;

use app\components\ImpactIcons;
use app\components\BookmarkIcon;

$item = $this->context;

?>

<div id="res_<?= $item->internal_id ?>" class="panel panel-default text-left compact-view">
    <div class="panel-body">
        <div class="row">
            <!-- title and basic info -->
            <div class="col-md-8">
                <?php if (isset($item->show["bookmark"]) && $item->show['bookmark']
                                && (!isset($item->edit_perm) || (isset($item->edit_perm) && $item->edit_perm))): ?>
                    <!-- bookmark -->
                    <span class="bookmark-item" style="cursor: pointer;">
                    <?= BookmarkIcon::widget([
                            'user_liked' => $item->user_id,
                            'user_logged' => Yii::$app->user->id,
                            'id_bookmark' => $item->internal_id
                        ]);
                    ?>
                    </span>
                <?php endif; ?>
                <?php
                    $params = ['id' => $item->doi];
                    if (isset($item) && isset($item->space_url_suffix)) {
                        $params['space_url_suffix'] = $item->space_url_suffix;
                    }
                    $url = Url::to(array_merge(['site/details'], $params));
                ?>
                <?= Html::a(
                    Yii::$app->bipstring->lowerize(Yii::$app->bipstring->shortenString($item->title, 120)) . ' <small><i class="fa fa-info-circle" aria-hidden="true"></i></small>',
                    $url,
                    ['class' => 'main-green', 'title' => 'Show details', 'target' => '_blank']
                ); ?>
                <?php if(!empty($item->retracted)): ?>
                    <i class="retraction-alert fa fa-exclamation-triangle" title="This article has been retracted"></i>
                <?php endif; ?>
                
                <div class="compact-meta">
                    <!-- authors -->
                    <div class="grey-text small" <?php if (strlen($item->authors) > 80) { ?> title="<?= $item->authors ?>" <?php } ?>>
                        <i class="fa-solid fa-user-group fa-fw" title="Authors"></i> <?= (trim($item->authors) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($item->authors, 80) ?>
                    </div>
                    
                    <!-- venue and year -->
                    <div class="grey-text small">
                        <span <?php if (strlen($item->journal) > 60) { ?> title="<?= $item->journal ?>" <?php } ?>>
                            <i class="fa-solid fa-book fa-fw" title="Venue"></i> <?= (trim($item->journal) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($item->journal, 60) ?>
                        </span>&middot;
                        <span><?= empty($item->year) ? 'N/A' : $item->year ?></span>
                    </div>
                </div>
            </div>

            <!-- impact indicators -->
            <div class="col-md-4 text-right">
                <div class="version-impact-icons-wrapper">
                    <?php if(!empty($item->dois_num) && $item->dois_num > 1): ?>
                        <span class="version-link-wrapper">
                            <a href="<?= Url::to(['site/get-versions', 'openaire_id' => $item->openaire_id]) ?>" modal-title="<i class=&quot;fas fa-clone&quot; aria-hidden=&quot;true&quot;></i> Other versions" data-remote="false" data-toggle="modal" data-target="#versions-modal" class="grey-link version-link">
                                <?= $item->dois_num ?> versions</a>
                        </span>
                    <?php endif; ?>
                    
                    <?= ImpactIcons::widget(['popularity_class' => $item->pop_class,
                                        'influence_class' => $item->inf_class,
                                        'impulse_class' => $item->imp_class,
                                        'cc_class' => $item->cc_class,
                                        'popularity_score' => $item->pop_score,
                                        'influence_score' => $item->inf_score,
                                        'impulse_score' => $item->imp_score,
                                        'cc_score' => $item->cc_score,
                                        'impact_indicators' => $item->impact_indicators,
                                        ]);?>
                </div>
            </div>
        </div>

        <!-- concepts (if enabled) -->
        <?php if (isset($item->show["concepts"]) && $item->show['concepts'] && !empty($item->concepts)): ?>
            <div class="compact-concepts grey-text small">
                <i class="fa-solid fa-atom fa-fw" aria-hidden="true" title="Topics"></i>
                <?php
                $concept_count = 0;
                foreach ($item->concepts as $concept) { 
                    if ($concept_count >= 3) break; // Show only first 3 concepts
                    echo '<span class="concept-tag">' . $concept['display_name'] . '</span>';
                    $concept_count++;
                }
                if (count($item->concepts) > 3) {
                    echo '<span class="concept-more">+' . (count($item->concepts) - 3) . ' more</span>';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- involvement (if enabled) -->
        <?php if (isset($item->show['involvement']) && $item->show['involvement']): ?>
            <?php if ($item->edit_perm): ?>
                <div class="compact-involvement grey-text small">
                    <i class="fa fa-briefcase fa-fw" aria-hidden="true" title="Contribution Roles based on the CRediT taxonomy"></i>
                    <span id="res_<?= $item->internal_id ?>_involvement_tags" class="tags-wrapper">
                        <?php if (!empty($item->involved)) {
                            foreach ($item->involved as $inv) {
                                echo '<span class="concept-tag" data-roleid="' . Html::encode($inv) . '">' . Html::encode($item->involvements[$inv]) . '</span>';
                            }
                        } ?>
                        <span class="involvement-region">
                            <?php
                                $options_inv = [];
                                foreach($item->involvements as $value => $field){
                                    $options_inv[$value] = ["data-content" => "<span class='label involvement'>$field</span>"];
                                }

                                echo Html::dropDownList("res_" . $item->internal_id . "_inv", $item->involved, $item->involvements, [
                                    'class' => 'selectpicker involvement-dropdown',
                                    'multiple' => '',
                                    'data-live-search' => "false",
                                    'title'=>"",
                                    'data-style'=>"btn-sm",
                                    'data-size'=>"7",
                                    'data-multiple-separator' => " ",
                                    'data-dropup-auto'=>"false",
                                    'data-width'=>"fit",
                                    'style' => 'display:none',
                                    'options' => $options_inv
                                ]);
                            ?>
                        </span>
                    </span>
                </div>
                <script>
                (function($){
                    $(function(){
                        var sel = $("select[name='res_<?= $item->internal_id ?>_inv']");
                        var tagsContainer = $('#res_<?= $item->internal_id ?>_involvement_tags');
                        var involvementRegion = tagsContainer.find('.involvement-region');
                        sel.on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                            var option = $(this).find('option').eq(clickedIndex);
                            var roleId = option.val();
                            var roleName = option.text();
                            if (isSelected) {
                                if (tagsContainer.find(".concept-tag[data-roleid='"+roleId+"']").length === 0) {
                                    // Insert before the involvement-region
                                    $('<span>', { 'class': 'concept-tag', 'data-roleid': roleId, text: roleName }).insertBefore(involvementRegion);
                                }
                            } else {
                                tagsContainer.find(".concept-tag[data-roleid='"+roleId+"']").remove();
                            }
                        });
                    });
                })(jQuery);
                </script>
            <?php else: ?>
                <div class="compact-involvement grey-text small">
                    <i class="fa fa-briefcase fa-fw" aria-hidden="true" title="Contribution Roles based on the CRediT taxonomy"></i>
                    <?php if (empty($item->involved)) : ?>
                        <span style="margin-left:5px;">-</span>
                    <?php else : ?>
                        <?php foreach ($item->involved as $inv) { ?>
                            <span class="concept-tag"><?= $item->involvements[$inv] ?></span>
                        <?php } ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- footer info -->
        <div class="compact-footer grey-text small">
            <span class="openness-info">
                <i class="fa-solid <?= Yii::$app->params['openness'][$item->is_oa]['icon_class'] ?>" aria-hidden="true" title="<?= Yii::$app->params['openness'][$item->is_oa]['name'] ?>"></i> <?= Yii::$app->params['openness'][$item->is_oa]['name'] ?>
            </span>&middot;
            <span class="work-type-info">
                <i class="fa-solid <?= Yii::$app->params['work_types'][$item->type]['icon_class'] ?>" aria-hidden="true" title="<?= Yii::$app->params['work_types'][$item->type]['title'] ?>"></i> <?= Yii::$app->params['work_types'][$item->type]['name']?>
            </span>
        </div>
    </div>
</div>

