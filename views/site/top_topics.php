<div class="grey-text tag-region">
    <div class="bootstrap-tagsinput">
        <?php
        if (empty($top_topics))
            echo "No topics found.";
        else {
            foreach ($top_topics as $topic_name => $facet_count) { ?>
                <span class="tag label topic-item" data-topic-name="<?= $topic_name ?>">
                    <?= $topic_name . ' (' . Yii::$app->formatter->asDecimal($facet_count, 0) . ')' ?>
                </span>
            <?php }
        } ?>
    </div>
</div>