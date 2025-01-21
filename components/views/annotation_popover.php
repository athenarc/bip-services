<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php foreach($this->context->data as $annotation_data): ?>
    <?php if ($annotation_data['label'] == 'id') {
        $annotation_id = $annotation_data['value'];
    } ?>
    <div>
        <span class='green-bip'><?= ucfirst($annotation_data['label']) ?>:</span>
        <?= empty(($annotation_data['value'])) ? 'N/A' : ucfirst(str_replace("\"", "'", $annotation_data['value'])) ?>
    </div>
<?php endforeach; ?>
    <div>
        <span class='green-bip'><?= 'Source' ?>:</span>
        <?= str_replace("\"", "'",  Yii::$app->params['annotation_dbs'][$this->context->space_annotation_db]['name'] . ' knowledge graph') ?>
    </div>

    <?php if ($this->context->has_reverse_annotation_query): ?>
        <div>
            <span class='green-bip'> All relevant works:</span>
            <a href='<?= Url::to(['site/annotation', 'annotation_id' => $annotation_id, 'space_url_suffix' => $this->context->space_url_suffix, 'space_annotation_id' => $this->context->space_annotation_id]) ?>' target='_blank'><i class='fa-solid fa-arrow-up-right-from-square'></i></a>
        </div>
    <?php endif; ?>

