<?php
/* 
 * View to display the papers found for a particular author
 * 
 * @author: Ilias Kanellos
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use app\components\CustomBootstrapRadioList;

$this->title = 'BIP! Finder - '. $author . " (author)"; 
?>
<div class="container">

    <div class='row'>
	<div class='article-header col-lg-12'>
            <div id="flex-parent">
                <div id="floating-title">
                    <span class="author-header">Author Name:&nbsp;</span><?= $author ?>
                </div>
            </div>
	</div>
    </div>  
    <div class='row author-details-row'>
        <div class='article-info col-md-12'>
            <b><?= (empty($author_papers)) ? "No" : $pagination->totalCount ?></b> articles were found associated with the name: <b><?= $author ?></b><br/>
            <?php if (!empty($author_papers))
            {
            ?>
            <div class="alert alert-warning"><strong>Note:</strong> The papers displayed may correspond to more than one author with the same name.</div>
            <?php
            }
            ?>
        </div>
    </div>
        
    <?php if ($found_results)
    {
        $form = ActiveForm::begin(['id' => 'author-radio-form', 'method'=>'post', 'action'=> Url::to(['site/author'])/*, 'options'=>['onsubmit'=>'$("#author_message").hide();$("#results_set").hide();$("#results_hdr").hide();$("#loading_results").show();']*/]);
    ?>
    <div class='row'>
        <div class="col-md-4 col-md-offset-4">
            <?= CustomBootstrapRadioList::widget(['name' => 'ordering', 'model' => $model, 'form' => $form, 'items' => ["popularity" => 'Popularity', 'influence' => 'Influence'], 'selected' => $ordering]); ?>
        </div>
        <input type="hidden" name="author" value="<?= $author ?>" />
    </div>
    <?php $form->end();  ?>   
    <div id="results_hdr" class='row'>
        <div class='col-md-4 text-center results-header'><?= $pagination->totalCount ?> results (<?= $pagination->pageCount ?> pages)</div>
        <div class='col-md-4 text-center'><?= LinkPager::widget(['pagination'=>$pagination,'maxButtonCount'=>5]); ?></div>
        <div class='col-md-4 text-center results-header'><i class="fa fa-lightbulb-o fa-lg" aria-hidden="true"></i> Click on entries for comparison</div>
    </div>   
    <div class='jumbotron'>
        <a href='<?=Url::to(['site/comparison'])?>' target='_blank' id='comparison' class='btn btn-warning'></a>
        <div id='clear-comparison' onclick="clearSelected();">
		Clear all<i class="fa fa-times" aria-hidden="true"></i>
        </div>
        <div class='row author-results-tbl-row'>
            <div class="col-md-12">
                <table id="results_set" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Venue</th>
                            <th>Year</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($author_papers as $row)
                    {
                    ?>
                        <tr id="res_<?= $row['internal_id'] ?>" class="text-left selected-after-click">
                            <td id="res_<?= $row['internal_id'] ?>_t"><?= Yii::$app->bipstring->lowerize(Yii::$app->bipstring->shortenString($row['title'],90)) ?></td>                                                
                            <td id="res_<?= $row['internal_id'] ?>_j">
                                <?= $row['journal'] ?>
                            </td>
                            <td id="res_<?= $row['internal_id'] ?>_y">
                                <?= $row['year'] ?>
                            </td>
                            <td>
                                <?php if ($row['user_id'] != null && Yii::$app->user->id != ''): ?>
                                <?= Html::a('<i class="fa-solid fa-bookmark liked-heart" aria-hidden="true"></i>', '#'/*Url::to(['site/unlike', 'paper_id' => $row['internal_id']])*/, ['class' => 'my-btn', 'id'=>'a_res_' . $row['internal_id'], 'title' => 'Remove from favorites.']); ?>
                                <?php elseif (Yii::$app->user->id == ''): ?>
                                <?= Html::a('<i class="fa-regular fa-bookmark" aria-hidden="true"></i>', Url::to(['site/likeunavailable']), ['data-method' => 'post', 'class' => 'my-btn', 'id' => $row['internal_id'], 'onclick' => 'function(){return false;}', 'title' => 'Add to favorites.']); ?>   
                                <?php else: ?>
                                <?= Html::a('<i class="fa-regular fa-bookmark not-liked-heart" aria-hidden="true"></i>', '#' /*Url::to(['site/like', 'paper_id' => $row['internal_id']])*/, ['class' => 'my-btn', 'id'=>'a_res_' . $row['internal_id'], 'title' => 'Add to favorites.']); ?>   
                                <?php endif; ?>
                                <?= Html::a('<i class="fa fa-info-circle" aria-hidden="true"></i>', ['site/redirect'], ['class' => 'my-btn', 'title' => 'Show details', 'target' => '_blank', 'data' => ['method' => 'post', 'params' => ['pmc'=> $row['pmc'], 'action' => 'details', 'keywords' => $author, 'paper_id' => $row['internal_id']]]]); ?>
                                <?= Html::a('<i class="fa fa-external-link-square" aria-hidden="true"></i>', ['site/redirect'], ['class' => 'my-btn', 'title' => 'Show article in PMC', 'target' => '_blank', 'data' => ['method' => 'post', 'params' => ['pmc'=> $row['pmc'], 'action' => 'pubmed', 'keywords' => $author, 'paper_id' => $row['internal_id']]]]); ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table> 
            </div>
        </div>
    </div>
    <?php } ?>