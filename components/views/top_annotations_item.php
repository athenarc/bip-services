<?php
use yii\helpers\Html;
?>
<div id="top_annotations" class="row grey-text">
    <div class="text-left col-md-2" style="font-size: 1.2em; white-space: nowrap;" title="List of the most common annotations related to the results displayed.">
        Key annotations <small><i class="fa fa-info-circle" aria-hidden="true"></i></small>:
    </div>
    <div class="col-md-10">
        <div style="display: inline-block; margin-right: 10px; margin-bottom: 5px;">
            <?= Html::dropDownList(
                'annotation_type_filter',
                'all', // Default value is 'all'
                array_merge(['all' => 'All'], $annotation_types ?? []),
                [
                    'id' => 'annotation_type_filter',
                    'class' => 'form-control',
                    'style' => 'display: inline-block; width: auto; min-width: 150px; padding: 1px 3px; height: 2em; font-size: 0.9em;',
                ]
            ) ?>
        </div>
        <div id="top_annotations_in_results" style="display: inline-block;">
            <!-- This will be populated by AJAX -->
            Loading...
        </div>
    </div>
</div>

<!-- Modal -->
<div id="top-annotations-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
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

