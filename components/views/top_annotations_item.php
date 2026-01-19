<?php
use yii\helpers\Html;

?>
<div id="top_annotations" class="row grey-text">
    <div class="text-left col-md-2" style="font-size: 1.2em;">
        <span style="white-space: nowrap;">
            Key annotations
            <small>
                <i
                    class="fa fa-info-circle"
                    aria-hidden="true"
                    title="List of the most common annotations related to the results displayed."
                ></i>
            </small>:
        </span>
        <div class="inline-block-d" style="margin: 5px 0 0 0;">
            <?php
            // Convert annotation IDs to strings to preserve them in dropdown
            // Html::dropDownList may convert numeric keys to 0, 1, 2... so we use string keys
            $dropdown_options = ['all' => 'All'];

            if (! empty($annotation_types)) {
                foreach ($annotation_types as $id => $name) {
                    $dropdown_options[(string) $id] = $name;
                }
            }
            ?>
            <?= Html::dropDownList(
                'annotation_type_filter',
                'all', // Default value is 'all'
                $dropdown_options,
                [
                    'id' => 'annotation_type_filter',
                    'class' => 'form-control',
                    'style' => ['display' => 'inline-block', 'width' => '160px', 'color' => 'grey'],
                ]
            ) ?>
        </div>
    </div>
    <div id="top_annotations_in_results" class="col-md-10">
        <!-- This will be populated by AJAX -->
        Loading...
    </div>
</div>

<!-- Modal -->
<div id="top-annotations-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title main-green" id="annotationModalLabel" style="font-size: 1.2em;">Annotation evolution</span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Loading...
            </div>
        </div>
    </div>
</div>

