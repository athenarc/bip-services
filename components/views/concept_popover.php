<?php

use yii\helpers\Html;
use yii\helpers\Url;



$item= $this->context;

?>


<div>
    <span class='green-bip'>Description:</span>
    <?= empty(($item->concept['description'])) ? 'N/A' : ucfirst(str_replace("\"", "'", $item->concept['description'])) . '.' ?>
</div>
<div>
    <a target='_blank' class='green-bip' href='<?= $item->concept['wikidata'] ?>'> Link to Wikidata <i class='fa fa-external-link-square' aria-hidden='true'></i></a>
</div>
<div>
    <span class='green-bip'>Confidence:</span>
    <?= empty(($item->concept['concept_score'])) ? 'N/A' : round($item->concept['concept_score'], 2)?>
</div>