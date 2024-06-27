<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php foreach($this->context->data as $annotation_data): ?>
    <div>
        <span class='green-bip'><?= ucfirst($annotation_data['label']) ?>:</span>
        <?= empty(($annotation_data['value'])) ? 'N/A' : ucfirst(str_replace("\"", "'", $annotation_data['value'])) ?>
    </div>
<?php endforeach; ?>
    <div>
        <span class='green-bip'><?= 'Source' ?>:</span>
        <?= str_replace("\"", "'",  Yii::$app->params['annotation_dbs'][$this->context->space_annotation_db]['name'] . ' knowledge graph') ?>
    </div>
