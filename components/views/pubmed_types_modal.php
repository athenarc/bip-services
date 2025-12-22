<?php
use yii\bootstrap\Modal;

Modal::begin([
    'id' => $modalId,
    'header' => '<h4 class="modal-title">Select NLM Types</h4>',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false,
    ],
    'footer' => '
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-custom-color" id="' . $applyButtonId . '">Apply</button>
    ',
]);
?>

<div style="max-height:400px; overflow-y:auto; overflow-x:hidden">
    <div class="row">
        <?php foreach ($pubmed_types_fields as $id => $label): ?>
            <div class="col-sm-6">
                <div class="checkbox checkbox-custom">
                    <?php
                    $inputId = $checkboxIdPrefix . $id;
                    $checked = in_array($id, $selectedTypes) ? 'checked' : '';
                    ?>
                    <input
                        id="<?= $inputId ?>"
                        type="checkbox"
                        class="<?= $checkboxClass ?>"
                        value="<?= $id ?>"
                        <?= $checked ?>
                    >
                    <label for="<?= $inputId ?>"><?= $label ?></label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php Modal::end(); ?>
