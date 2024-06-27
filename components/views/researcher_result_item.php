<?php

use yii\helpers\Html;
use yii\helpers\Url;

use app\components\ImpactIcons;
use app\components\BookmarkIcon;
use app\components\ConceptPopover;
use app\components\AnnotationPopover;

$item = $this->context;

?>

<div id="res_<?= $item->id ?>" class="panel panel-default text-left">
    <div class="panel-heading">
        <div class="row">
            <!-- name -->
            <div id="res_<?= $item->id ?>_t" class="col-md-8 col-lg-9"
                <?php if (strlen($item->name) > 90) { ?> title="<?= $item->name ?>" <?php } ?>>
                
                <?= Html::a(
                    Yii::$app->bipstring->lowerize(Yii::$app->bipstring->shortenString($item->name, 90)) . ' <small><i class="fa fa-info-circle" aria-hidden="true"></i></small>',
                    Url::to(array_merge(['scholar/profile'], [ 'orcid' => $item->orcid])),
                    ['class' => 'main-green', 'title' => 'Show researcher profile', 'target' => '_blank']
                ); ?>
            </div>
        </div>
    </div>

    <div class="panel-body">

        <div>
            <!-- orcid -->
            <span id="res_<?= $item->id ?>_o" class="grey-text">
            <i class="fa-brands fa-orcid" title="ORCiD"></i> <a class="grey-link" href = "<?= "https://orcid.org/" . $item->orcid ?>" target="_blank"> <?= $item->orcid ?> </a>
        </div>
    </div>
</div>