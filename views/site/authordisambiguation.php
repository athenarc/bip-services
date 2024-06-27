<?php

/* 
 * Page to choose among multiple authors with the same name
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

?>
<div class="container">
    
    <div class='row'>
	<div class='article-header col-lg-12'>
            <div id="flex-parent">
                <div id="floating-title">
                    <span class="author-header">Multiple names associated with: </span><?= ucfirst($author) ?>
                </div>
            </div>
	</div>
    </div> 
    
    <div class='jumbotron'>
        
        <div id="results_hdr" class='row'>
            <div class='col-md-4 text-center results-header'><?= $pagination->totalCount ?> results (<?= $pagination->pageCount ?> pages)</div>
            <div class='col-md-4 text-center'><?= LinkPager::widget(['pagination'=>$pagination,'maxButtonCount'=>5]); ?></div>
            <div class='col-md-4 text-center results-header'><i class="fa fa-lightbulb-o fa-lg" aria-hidden="true"></i> Select an author name from the list</div>
        </div>          
        
        <div class='row author-results-tbl-row'>
            <div class="col-md-12">
                <table id="results_set" class="table table-hover disambiguation">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Venue(s) Published</th>
                            <th>Active Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($synonym_list as $synonym)
                        {
                        ?>
                        <tr>
                            <td><a href="<?= Url::to(['site/author', 'author' => $synonym]); ?>"><?= htmlspecialchars($synonym) ?></a></td>
                            <td><?= implode(", ", array_slice($author_stats_array[$synonym]['journals'], 0,3)); if(count($author_stats_array[$synonym]['journals']) > 3) echo ", and " . (count($author_stats_array[$synonym]['journals'])-1) . " more journal(s)"; ?></td>
                            <td><?= $author_stats_array[$synonym]['active_periods'][0]; ?> - <?= $author_stats_array[$synonym]['active_periods'][1]; ?></td>
                        </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>