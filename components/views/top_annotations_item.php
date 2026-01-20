<?php
use yii\helpers\Html;

?>
<div id="top_annotations" class="row grey-text" style="margin-bottom: 15px; align-items: center;">
    <style>
        @media (max-width: 768px) {
            #top_annotations > .col-md-12 > div:first-child {
                flex-wrap: wrap;
                align-items: flex-start;
            }
            #top_annotations .dropdown-wrapper {
                width: 100%;
                margin-top: 5px;
                margin-left: 0;
                display: flex;
                justify-content: flex-start;
            }
        }
    </style>
    <div class="col-md-12" style="display: flex; align-items: center; flex-wrap: nowrap; gap: 10px; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 5px; font-size: 1.2em; font-weight: 500; white-space: nowrap; flex-shrink: 0;">
                <span>Key annotations</span>
                <i class="fa fa-info-circle" aria-hidden="true" title="List of the most common annotations related to the results displayed." style="font-size: 0.9em; opacity: 0.7;"></i>
            </div>
            <div class="dropdown-wrapper" style="flex-shrink: 0;">
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
                        'style' => [
                            'display' => 'inline-block', 
                            'width' => '70px', 
                            'color' => 'grey', 
                            'font-size' => '11px',
                            'padding' => '1px 5px',
                            'height' => 'auto',
                            'line-height' => '1.5',
                            'border-radius' => '3px',
                            'border' => '1px solid #ccc',
                            'background-color' => '#fff',
                        ],
                    ]
                ) ?>
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

