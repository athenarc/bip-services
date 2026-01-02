<div class="grey-text tag-region">
    <div class="bootstrap-tagsinput">
        <?php
        if (empty($top_annotations)) {
            echo 'No annotations found.';
        } else {
            foreach ($top_annotations as $annotation_name => $facet_count) { ?>
                <span class="tag label annotation-item" data-annotation-name="<?= $annotation_name ?>">
                    <?= ucfirst($annotation_name) . ' (' . Yii::$app->formatter->asDecimal($facet_count, 0) . ')' ?>
                </span>
            <?php }
        } ?>
    </div>
</div>

