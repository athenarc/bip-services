<?php

// Global modal to warn mobile user

use yii\bootstrap\Modal;

$footer = '
<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>

';

Modal::begin([  'options' => ['class' => ''],
                'id' => 'warnMobileUser',
                'size' => 'modal-lg',
                'closeButton' => False,
                'footer' => $footer
            ]);
echo "BIP! is not optimised for mobile devices";
Modal::end();
?>
