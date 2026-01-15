<?php
use yii\helpers\Html;

// Default: use main-color (CSS var) unless a specific type is selected
$borderColor = 'var(--main-color)';

if (isset($selected_annotation_type_id, $annotation_type_colors)
    && $selected_annotation_type_id !== null
    && isset($annotation_type_colors[$selected_annotation_type_id])
) {
    $borderColor = $annotation_type_colors[$selected_annotation_type_id];
}
?>

<div class="grey-text tag-region">
    <div class="bootstrap-tagsinput">
        <?php
        if (empty($top_annotations)) {
            echo 'No annotations found.';
        } else {
            foreach ($top_annotations as $annotation_name => $facet_count) { ?>
                <span
                    class="tag label annotation-item"
                    data-annotation-name="<?= Html::encode($annotation_name) ?>"
                    style="border-color: <?= Html::encode($borderColor) ?>;"
                >
                    <?= Html::encode(ucfirst($annotation_name)) . ' (' . Yii::$app->formatter->asDecimal($facet_count, 0) . ')' ?>
                </span>
            <?php }
        } ?>
    </div>
</div>

