<?php

// Global modal when a bookmark is about to be deleted

use yii\bootstrap\Modal;

$footer = '
<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
<button type="button" id="deletebookmark" class="btn btn-danger">Delete</button>
';

Modal::begin([
                'id' => 'confirm-delete-bookmark',
                'size' => '',
                'closeButton' => False,
                'footer' => $footer
            ]);
echo "Are you sure you want to delete this bookmark ?";
Modal::end();
?>
