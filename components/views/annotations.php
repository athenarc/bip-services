<?php

/*
 * Annotations view - displays annotations grouped by type
 *
 * (First Version: Dec 2024)
 */

use app\components\AnnotationPopover;
use yii\helpers\Html;

?>

<div id="res_<?= $internal_id ?>_annot">
    <?php
    $tab_container_id = "res_{$internal_id}_annot_tabs";
    ?>
    
    <!-- Tab Headers -->
    <div class="annotation-tabs tag-region grey-text">
        <i class="fa-solid fa-tag fa-fw" aria-hidden="true" title="Annotations"></i>
        <?php foreach ($grouped_annotations as $annotation_id => $group): ?>
            <?php
            $tab_id = "res_{$internal_id}_annot_tab_{$annotation_id}";
            $content_id = "res_{$internal_id}_annot_content_{$annotation_id}";
            ?>
            <a class="annotation-tab btn btn-default btn-xs grey-link" 
                 id="<?= $tab_id ?>"
                 data-content-id="<?= $content_id ?>"
                 onclick="switchAnnotationTab('<?= $tab_container_id ?>', '<?= $tab_id ?>', '<?= $content_id ?>'); return false;"
                 href="#"
                 role="button">
                <?= Html::encode($group['type_name']) ?> <span class="badge badge-primary"><?= count($group['items']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Tab Content -->
    <div id="<?= $tab_container_id ?>" class="annotation-tab-content">
        <?php
        foreach ($grouped_annotations as $annotation_id => $group):
            $content_id = "res_{$internal_id}_annot_content_{$annotation_id}";
        ?>
            <div class="tag-region grey-text annotation-content" id="<?= $content_id ?>">
                <div class="bootstrap-tagsinput">
                    <span class="annotation-group-label" style="background-color: <?= Html::encode($group['type_color']) ?>;"><?= Html::encode($group['type_name']) ?></span>
                    <?php foreach ($group['items'] as $annotation): ?>
                        <span class="tag label">
                            <?php $annotation_content = AnnotationPopover::widget([
                                'data' => $annotation['data'],
                                'space_annotation_db' => $space_annotation_db,
                                'space_url_suffix' => $space_url_suffix,
                                'annotation_type_id' => $annotation['annotation_id'],
                                'paper_id' => $internal_id,
                                'annotation_name' => $annotation['label'],
                                'annotation_id' => $annotation['id'] ?? null,
                                'enable_like_dislike_annotations' => $enable_like_dislike_annotations ?? false,
                                'has_graph_entity_fields' => $annotation['has_graph_entity_fields'] ?? false
                            ]); ?>
                            <span role="button" data-toggle="popover" data-placement="auto" title="<b><?= $annotation['label'] ?> <i class='fa fa-info-circle' aria-hidden='true' title='<?=Html::encode($annotation['annotation_description'])?>'></i></b>" data-content="<?= $annotation_content ?>"><?= $annotation['label'] ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>