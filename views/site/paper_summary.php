<?php
/* 
 * Display paper title, year, and outlink
 */
use yii\helpers\Url;
use yii\helpers\Html;
?>

<p>
    <span class='details-header'>Title: </span>
    <span class='details-title'><?= $article->title ?></span>
</p>
<p>
    <span class='details-header'>Authors: </span>
    <span class='details-authors'><?= $article->authors ?></span>
</p>
<p>
    <span class='details-header'>Journal: </span>
    <span class='details-journal'><?= $article->journal ?></span>
</p>
<p>
    <span class='details-header'>Year: </span>
    <span class='details-year'><?= $article->year ?></span>
</p>
<p>
    <span class='details-header'>External links: </span>
    <span class='details-outlink'>
        <a href="http://www.ncbi.nlm.nih.gov/pmc/articles/<?= $article->pmc?>/" target='_blank' class="text-success">PMC
            <i class="fa fa-external-link-square text-success" aria-hidden="true"></i>
        </a>
    </span>
</p>
<p>
    <span class='details-header'>BIP! details: </span>
    <span class='details-inlink'>
        <a href='<?= Url::to(['site/details', 'id'=> $article->pmc]) ?>' target='_blank' class="text-success">
            <i class="fa fa-info-circle text-success" aria-hidden="true"></i>
        </a>
    </span>
</p>
