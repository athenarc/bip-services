<?php
use yii\helpers\Html;

?>
<div id="top_annotations" class="row grey-text">
    <div class="col-md-12 top-annotations-row">
        <div class="top-annotations-inner">
            <div class="dropdown-wrapper">
                <span>Key</span>
                <?php
                // Convert annotation IDs to strings to preserve them in dropdown
                $dropdown_options = ['all' => 'Annotations'];

                if (! empty($annotation_types)) {
                    foreach ($annotation_types as $id => $name) {
                        $dropdown_options[(string) $id] = $name;
                    }
                }
                ?>
                <?= Html::dropDownList(
                    'annotation_type_filter',
                    'all',
                    $dropdown_options,
                    [
                        'id' => 'annotation_type_filter',
                        'class' => 'form-control',
                        'style' => [
                            'display' => 'inline-block',
                            'color' => 'grey',
                            'padding' => '1px 5px',
                            'height' => 'auto',
                            'line-height' => '1.5',
                            'border-radius' => '3px',
                            'border' => '1px solid #ccc',
                            'background-color' => '#fff',
                        ],
                    ]
                ) ?>
                <i class="fa fa-info-circle" aria-hidden="true" title="List of the most common annotations related to the results displayed." style="font-size: 0.9em; opacity: 0.7;"></i>
            </div>
            <div id="top_annotations_in_results" style="flex: 1; min-width: 0; overflow: hidden; display: flex; align-items: center; justify-content: flex-start; gap: 8px;">
                <!-- This will be populated by AJAX -->
                <i class="fa fa-spinner fa-spin grey-text"></i>
                <span>Loading...</span>
            </div>
        </div>
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

