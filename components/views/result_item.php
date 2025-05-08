<?php

use yii\helpers\Html;
use yii\helpers\Url;

use app\components\ImpactIcons;
use app\components\BookmarkIcon;
use app\components\ConceptPopover;
use app\components\AnnotationPopover;

$item = $this->context;

?>

<div id="res_<?= $item->internal_id ?>" class="panel panel-default text-left">
    <div class="panel-heading">
        <div class="row">
            <!-- title -->
            <div id="res_<?= $item->internal_id ?>_t" class="col-md-8 col-lg-9 <?= (!empty($item->retracted)) ? 'retraction-alert' : ''?>"

                <?php if (strlen($item->title) > 90) { ?> title="<?= $item->title ?>" <?php } ?>>

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
                    Yii::$app->bipstring->lowerize(Yii::$app->bipstring->shortenString($item->title, 90)) . ' <small><i class="fa fa-info-circle" aria-hidden="true"></i></small>',
                    $url,
                    ['class' => 'main-green', 'title' => 'Show details', 'target' => '_blank']
                ); ?>
                <?php if(!empty($item->retracted)): ?>
                    <i class="retraction-alert fa fa-exclamation-triangle" title="This article has been retracted"></i>
                <?php endif; ?>
            </div>

            <div class="col-md-4 col-lg-3 text-right">
                <div class="citation-impact-icons">
                    <?php if(!empty($item->dois_num) && $item->dois_num > 1): ?>
                            <a href="<?= Url::to(['site/get-versions', 'openaire_id' => $item->openaire_id]) ?>" modal-title="<i class=&quot;fas fa-clone&quot; aria-hidden=&quot;true&quot;></i> Other versions" data-remote="false" data-toggle="modal" data-target="#versions-modal" class="grey-link version-link">
                                Found <?= $item->dois_num ?> versions</a>
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
                                        'options' => ['mode' => 'compact', 'showScoreLabel' => false]
                                        ]);?>
                </div>
            </div>
        </div>
    </div>


    <div class="panel-body">

        <!-- authors -->
        <div id="res_<?= $item->internal_id ?>_a" class="grey-text" <?php if (strlen($item->authors) > 100) { ?> title="<?= $item->authors ?>" <?php } ?>>
            <i class="fa-solid fa-user-group fa-fw" title="Authors"></i> <?= (trim($item->authors) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($item->authors, 100) ?>
        </div>
        <div>
            <!-- venue -->
            <span id="res_<?= $item->internal_id ?>_j" class="grey-text" <?php if (strlen($item->journal) > 100) { ?> title="<?= $item->journal ?>" <?php } ?>>
            <i class="fa-solid fa-book fa-fw" title="Venue"></i> <?= (trim($item->journal) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($item->journal, 100) ?>
            </span>&middot;

            <!-- year -->
            <span id="res_<?= $item->internal_id ?>_y" class="grey-text">
                <?= empty($item->year) ? 'N/A' : $item->year ?>
            </span>
        </div>
        <?php if (isset($item->show["concepts"]) && $item->show['concepts']): ?>
        <!-- concepts -->
            <div id="res_<?= $item->internal_id ?>_conc" class="tag-region grey-text">
                <div class="bootstrap-tagsinput">
                    <i class="fa-solid fa-atom fa-fw" aria-hidden="true" title="Topics"></i>
                    <?php
                    if (empty($item->concepts))
                        echo "&nbspN/A";
                    else {
                        foreach ($item->concepts as $concept) { ?>
                            <span class="tag label concept-tag" >
                                <?php $data_content = ConceptPopover::widget(['concept' => $concept]);?>
                                <span role="button" data-toggle="popover" data-placement="auto" title="<b><?= $concept['display_name'] ?> </b>" data-content="<?= $data_content ?>"><?= $concept['display_name'] ?></span>
                                <span class= "concept-confidence" title = "Confidence: <?= round($concept['concept_score'],2) ?>" ><i class="fa-concept-confidence fa-solid fa-circle" style = "background-image: linear-gradient(to right, var(--main-color) <?= 100*round($concept['concept_score'],2) ?>%, #ddd 0%);"></i></span>
                                <span class="concept-divider">
                                    <span style="color: #808080;">|</span> 
                                    <?= ImpactIcons::widget([
                                        'popularity_class' => $concept['pop_class'],
                                        'influence_class' => $concept['inf_class'],
                                        'impulse_class' => $concept['imp_class'],
                                        'cc_class' => $concept['cc_class'],
                                        'popularity_score' => $item->pop_score,
                                        'influence_score' => $item->inf_score,
                                        'impulse_score' => $item->imp_score,
                                        'cc_score' => $item->cc_score,
                                        'impact_indicators' => $item->impact_indicators,
                                        'options' => ['mode' => 'compact', 'showScoreLabel' => false],
                                    ]);?>
                                </span>
                            </span>
                        <?php }
                    } ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($item->show["relations"]) && $item->show['relations']): ?>
            <!-- relations -->
            <?php if (!empty($item->relations)): ?>
                <div id="res_<?= $item->internal_id ?>_rel" class="tag-region grey-text">
                    <div class="bootstrap-tagsinput">
                    <i class="fa-solid fa-paperclip fa-fw" aria-hidden="true" title="Relations"></i>

                    <?php foreach ($item->relations as $relation) { ?>
                        <span class="tag label">
                            <span role="button" href="<?= Url::to(['site/get-relations-data', 'target_dois' => $relation['target_dois'], 'source_openaire_id' => $item->openaire_id]) ?>" data-toggle="modal" data-remote="false" modal-title="Related works" data-target="#relations-modal"><?= $relation['type'] ?> <span class="badge badge-primary" style ="top: -1px;padding: 1px 5px; position: relative;"><?= count($relation['target_dois'])?></span></span>
                            
                        </span>
                    <?php } ?>

                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (isset($item->show["annotations"]) && $item->show['annotations']): ?>
        <!-- annotations -->
            <?php if (!empty($item->annotations)): ?>
                <div id="res_<?= $item->internal_id ?>_annot" class="tag-region grey-text">
                    <div class="bootstrap-tagsinput">
                    <i class="fa-solid fa-tag fa-fw" aria-hidden="true" title="Annotations"></i>

                    <?php foreach ($item->annotations as $annotation) { ?>
                        <span class="tag label">
                            <?php $annotation_content = AnnotationPopover::widget([ 'data' => $annotation['data'], 'space_annotation_db' => $item->space_annotation_db, 'space_url_suffix' => $item->space_url_suffix, 'space_annotation_id' => $annotation['annotation_id'], 'has_reverse_annotation_query' => $annotation['has_reverse_query'] ]); ?>
                            <span role="button" data-toggle="popover" data-placement="auto" title="<b><?= $annotation['label'] ?> <i class='fa fa-info-circle' aria-hidden='true' title='<?=Html::encode($annotation['annotation_description'])?>'></i></b>" data-content="<?= $annotation_content ?>"><?= $annotation['label'] ?></span>
                            <?php if (!empty($annotation['annotation_color'])):?>
                                <span><i class="fa-solid fa-circle" style = "background-color:transparent;color:<?= $annotation['annotation_color'] ?>"></i></span>
                            <?php endif; ?>
                        </span>
                    <?php } ?>

                    </div>
                </div>
            <?php endif; ?>


        <?php endif; ?>

        <!-- tags -->
        <?php if (isset($item->show["tags"]) && $item->show['tags']): ?>

            <div class="tag-region grey-text">
                <?php if ($item->edit_perm): ?>
                    <input name="res_<?= $item->internal_id ?>_tags" class="tag-options" value="<?= $item->tags ?>" type="text" data-role="tagsinput" placeholder="+" autocomplete="off">
                <?php else: ?>
                    <div class="bootstrap-tagsinput">
                        <i class="fa fa-tags fa-fw" aria-hidden="true" title="User-provided tags"></i>
                        <?php
                        if (empty($item->tags))
                            echo "-";
                        else {
                            foreach (explode(',', $item->tags) as $tag) { ?>
                                <span class="tag label"><?= $tag ?></span>
                            <?php }
                        } ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($item->show['involvement']) && $item->show['involvement']): ?>

            <?php if ($item->edit_perm): ?>
                <div class="involvement-region grey-text">
                    <i class="fa fa-briefcase fa-fw" aria-hidden="true" title="Contribution Roles based on the CRediT taxonomy"></i>
                    <?php
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
                </div>
            <?php else: ?>
                <div class="tag-region grey-text">
                    <div class="bootstrap-tagsinput">
                        <i class="fa fa-briefcase fa-fw" aria-hidden="true" title="Contribution Roles based on the CRediT taxonomy"></i>
                        <?php if (empty($item->involved)) : ?>
                            <span style= "margin-left:5px;">-</span>
                        <?php else : ?>
                            <?php foreach ($item->involved as $inv) { ?>
                                <span class="tag label"><?= $item->involvements[$inv] ?></span>
                            <?php } ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- reading-status -->
        <?php if (isset($item->show['reading_status']) && $item->show['reading_status'] && $item->edit_perm): ?>
            <div class="rd_status-region grey-text">
                <i class="fas fa-glasses fa-fw" aria-hidden="true" title="Reading status"></i>
                <?php
                    echo Html::dropDownList("res_" . $item->internal_id . "_reading-status", $item->reading_status, $item->reading_status_choices, [
                        'class' => "reading-status reading-status-color",
                        'data-color'=> $item->reading_status,
                    ]);
                ?>
            </div>
        <?php endif; ?>

    </div>
    <div class="panel-footer">
        <div class="result-footer grey-text">

            <!-- openess -->
            <div class="flex-b-18 no-white-space text-center">
                <span>
                    <i class="fa-solid <?= Yii::$app->params['openness'][$item->is_oa]['icon_class'] ?>" aria-hidden="true" title = "<?= Yii::$app->params['openness'][$item->is_oa]['name'] ?>"></i> <?= Yii::$app->params['openness'][$item->is_oa]['name'] ?>
                </span>
            </div>

            <!-- work type -->
            <div class="flex-b-18 no-white-space text-center">
                <span>
                    <i class="fa-solid <?= Yii::$app->params['work_types'][$item->type]['icon_class'] ?>" aria-hidden="true" title = "<?= Yii::$app->params['work_types'][$item->type]['title'] ?>"></i> <?= Yii::$app->params['work_types'][$item->type]['name']?>
                </span>
            </div>


            <?php if (isset($item->show['search_context']) && $item->show['search_context']): ?>
                <!-- search context -->
                <div class="flex-b-18 no-white-space text-center">
                    <a class="context-popup-link btn btn-default btn-xs fs-inherit grey-link"  data-target="#context-modal" data-toggle="modal" href="#modal" title='Show matching context'>
                        <i class="fa fa-eye" aria-hidden="true"></i> Search Context
                    </a>

                    <!-- this is displayed in the content of the context modal -->
                    <div id="res_<?= $item->internal_id ?>_context" class="kwd-context title">
                        <?php if (!empty($item->search_context['author'])) { ?>
                            <span class="context-list-title">Author:</span>
                            <ul>
                            <?php foreach($item->search_context['author'] as $context) {
                                echo "<li>" . $context . "</li>";
                            } ?>
                            </ul>
                        <?php }
                        if(!empty($item->search_context["title"])) { ?>
                            <span class="context-list-title">Title:</span>
                            <ul>
                            <?php foreach($item->search_context["title"] as $context) {
                                echo "<li>" . $context . "</li>";
                            } ?>
                            </ul>
                        <?php }
                        if (!empty($item->search_context["abstract"])) { ?>
                            <span class="context-list-title">Abstract:</span>
                            <ul>
                            <?php foreach($item->search_context["abstract"] as $context) {
                                echo "<li>" . $context . "</li>";
                            } ?>
                            </ul>
                        <?php  }
                        ?>
                    </div>
                </div>
            <?php endif; ?>


            <?php if (isset($item->show["notes"]) && $item->show['notes'] && $item->edit_perm): ?>
                <!-- notes -->
                <div class="flex-b-18 no-white-space text-center">
                    <?php
                        echo Html::a( (!empty($item->notes)) ? '<i class="fa-solid fa-pen-to-square"></i> Add notes' : '<i class="fa-regular fa-pen-to-square"></i> Add notes', Url::to(['readings/load-notes', 'paper_id' => $item->internal_id]), ['class' => 'show-notes btn btn-default btn-xs fs-inherit grey-link', 'id' => 'notes-' . $item->internal_id, 'title' => 'Notes on the article', 'data-toggle' => "modal",  'data-target'=>"#text-editor-modal"]);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($item->show['comparison']) && $item->show['comparison']): ?>
                <!-- comparison -->
                <div class="flex-b-18 no-white-space text-center">
                    <span id="res_<?= $item->internal_id ?>_comp" class="selected-after-click" style="cursor: pointer;" title="Select for comparison"><i class="fa-solid fa-down-left-and-up-right-to-center"></i> Compare</span>
                </div>
            <?php endif; ?>

            <?php if (isset($item->show["copy_link"]) && $item->show['copy_link']): ?>
                <!-- copy_link -->
                <div class="flex-b-18 no-white-space text-center">
                    <!-- <a class="copy-link btn btn-default btn-xs fs-inherit grey-link" role = "button" target="_blank" href="<?=Url::to(['site/details', 'id' => $item->doi], true)?>" data-toggle="tooltip"> -->
                    <a class="copy-link btn btn-default btn-xs fs-inherit grey-link" role = "button" target="_blank" href="<?=Url::to(array_merge(['site/details'], $params), true)?>" data-toggle="tooltip">
                        <i class="fa-solid fa-copy" aria-hidden="true"></i> Copy Link
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>