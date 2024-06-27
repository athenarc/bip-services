<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\web\View;
use app\components\ImpactIcons;
use yii\bootstrap\Modal;
use app\components\BookmarkIcon;

$this->title = 'BIP! Finder - User bookmarks';
$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/deleteFolder.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/reading-status.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/favorites.css');
?>

<!-- Display the heading. -->
<div class="jumbotron" style="padding: 25px; margin-bottom: 0px;">
    <!-- <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <h2>
            <?php
                $u_name = Yii::$app->user->identity->username;
                if(substr($u_name,-1)=="s")
                    echo $u_name."' bookmarked papers";
                else
                    echo $u_name."'s bookmarked papers";
            ?>
            </h2>
        </div>
    </div> -->

    <a href='<?=Url::to(['site/comparison'])?>' target='_blank' id='comparison' class='btn btn-warning'></a>
    <div id='clear-comparison' onclick="clearSelected();">
        Clear all<i class="fa fa-times" aria-hidden="true"></i>
    </div>
</div>

<!-- no bookmarked papers -->
<?php if ($user_likes_num == 0) { ?>
    <div class="alert alert-warning text-center">You have no bookmarks yet! <i class="fa fa-question-circle" aria-hidden="true" title="To bookmark papers, click on the bookmark-shaped button of a paper in your search results."></i></div>
<?php return; } ?>

<!-- Add folder button -->
<div class="row">
    <a href="<?=Url::to(['site/createfolder'])?>" class="btn btn-primary">
        <i class="fa fa-folder" aria-hidden="true"></i> New folder
    </a>
</div>
<!-- List folders -->
<?php foreach ($folders as $folder) { ?>
    </br>
    <div class="row">
        <span><h4 style="display: inline-block;"><i class="fa fa-folder-o" aria-hidden="true"></i> <?= $folder['name'] ?></h4></span>
        (<span class = "folder-articles" id = "fa_<?= $folder['id'] ?>" >
        <?php
        $folder_articles = $folders_info[$folder['id']]["num_articles"];
        $folder_read = $folders_info[$folder['id']]["total_read"];
        echo $folder_articles.(($folder_articles != 1) ? " articles" : " article");
        echo empty($folder_articles) ? "" : ' - '.round(100*($folder_read/$folder_articles),0). '% read';
        ?>
        </span>)
        <form class='small-btn-form' id='comm-form' method='post' action='<?=Url::to(['site/editfolder'])?>' style="display: inline-block;">
            <input type="hidden" name="folder_id" id="edit_folder_id" value="<?=$folder->id?>" />
            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
            <!-- OLD edit folder BUTTON -->
            <!-- <button class="btn btn-xs bookmark-edit-button btn-primary" type="submit" title="Edit folder"> -->
            <button class="btn bookmark-edit-button" type="submit" title="Edit folder">
                <i class="fa fa-pencil" aria-hidden="true"></i>
            </button>
        </form>
        <form class='small-btn-form' id='formfield' method='post' action='<?=Url::to(['site/removefolder'])?>' style="display: inline-block;">
            <input type="hidden" name="folder_id" id="delete_folder_id" value="<?=$folder->id?>" />
            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
            <button class="btn bookmark-delete-button" type="button" title="Delete folder" value="<?= $folder['name']?>" >
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </form>
    </div>

    <?php if(empty($folders_contents[$folder['id']])) { ?>
        <p>No bookmarks in this folder</p>
    <?php continue; } ?>

    <table class="table table-hover" data-folderid = "<?= $folder['id']?>">

        <tbody>

        <?php foreach($folders_contents[$folder['id']] as $cur_contents) { ?>
            <tr id="res_<?= $cur_contents['internal_id'] ?>" class="text-left selected-after-click">
                <td class="col-xs-8">
                    <!-- title -->
                    <div id="res_<?= $cur_contents['internal_id'] ?>_t" <?php if (strlen($cur_contents['title']) > 90) { ?> title="<?= $cur_contents['title'] ?>" <?php } ?>>
                        <?= Yii::$app->bipstring->shortenString($cur_contents['title'], 90) ?>
                        <?= Html::a('<i class="fa fa-info-circle" aria-hidden="true"></i>', Url::to(['site/details', 'id' => $cur_contents['doi']]), ['class' => 'grey-link', 'id' => 'a_res_' . $cur_contents['internal_id'], 'title' => 'Show details', 'target' => '_blank']); ?>
                    </div>

                    <div class="year-venue-bookmarks" id="res_<?= $cur_contents['internal_id'] ?>_jy">
                        <!-- venue -->
                        <span id="res_<?= $cur_contents['internal_id'] ?>_j" <?php if (strlen($cur_contents['journal']) > 60) { ?> title="<?= $cur_contents['journal'] ?>" <?php } ?>>
                            <?= (trim($cur_contents['journal']) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($cur_contents['journal'], 60)?>
                        </span>&middot;
                        <!-- year -->
                        <span id="res_<?= $cur_contents['internal_id'] ?>_y">
                            <?= ($cur_contents['year'] == 0) ? "N/A" : $cur_contents['year'] ?>
                        </span>
                    </div>
                </td>
                <td class="col-xs-1" style = "padding-top: 12px;">
                    <select class="reading-status" data-color = "<?= $cur_contents['reading_status']?>">
                        <option value="0" <?= ($cur_contents['reading_status'] == 0) ? "selected" : "" ?>>To Read</option>
                        <option value="1" <?= ($cur_contents['reading_status'] == 1) ? "selected" : "" ?>>Reading</option>
                        <option value="2" <?= ($cur_contents['reading_status'] == 2) ? "selected" : "" ?>>Read</option>
                    </select>

                </td>
                <td class="col-xs-1">
                    <form div class='col-xs-2' id='comm-form' method='post' action='<?=Url::to(['site/movefolder'])?>' title='Move this article'>
                        <input type='hidden' name='bookmark_id' id='bookmark_id' value="<?=$cur_contents['id']?>"/>
                        <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
                        <button type="submit" class="btn btn-default btn-sm">
                            <i class="fa fa-arrows" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
                <!-- impact -->
                <td class="col-xs-1">
                    <?= ImpactIcons::widget(['popularity_class' => $cur_contents['pop_class'],
                                    'influence_class' => $cur_contents['inf_class'],
                                    'impulse_class' => $cur_contents['imp_class']]);?>
                </td>
                <!-- bookmark -->
                <td class="col-xs-1" style="text-align: right; width: 1%" >

                    <?= BookmarkIcon::widget(['user_liked' => True, /*by default all bookmarks are liked by the user*/
                                            'user_logged' => True, /*by default user is logged-in in favorites view */
                                            'id_bookmark' => $cur_contents['internal_id']]);?>


                    <!-- <?= Html::a('<i class="fa fa-info-circle" aria-hidden="true"></i>', Url::to(['site/details', 'id' => $cur_contents['doi']]), ['class' => 'my-btn', 'id' => 'a_res_' . $cur_contents['internal_id'], 'title' => 'Show details', 'target' => '_blank']); ?> -->

                    <!-- <?= Html::a('<i class="fa fa-external-link-square" aria-hidden="true"></i>', Url::to('https://doi.org/' . $cur_contents['doi']), ['class' => 'my-btn', 'id' => 'a_res_' . $cur_contents['internal_id'], 'title' => 'Show article', 'target' => '_blank']); ?> -->
                </td>
            </tr>
        <?php } ?>

        </tbody>
    </table>
<?php } ?>


<!-- Not bookmarked papers -->
<?php if (!empty($bmk_uncategorized)) { ?>

    </br>
    <div class="row">
        <h4>Misc. bookmarks</h4>
    </div>
    <div id="results_hdr" class='row'>
        <div class='col-xs-4 text-center results-header'><?= $pagination->totalCount ?> articles (<?= $pagination->pageCount ?> pages)</div>
        <div class='col-xs-4 text-center'><?= LinkPager::widget(['pagination' => $pagination,'maxButtonCount'=>5]); ?></div>
        <!-- <div class='col-md-4 text-center results-header'><i class="fa fa-lightbulb-o fa-lg" aria-hidden="true"></i> Click on entries for comparison</div> -->
    </div>
    <table class="table table-hover">

        <tbody>

        <?php foreach($bmk_uncategorized as $cur_contents) { ?>
            <tr id="res_<?= $cur_contents['internal_id'] ?>" class="text-left selected-after-click">
                <td class="col-xs-8">
                    <!-- title -->
                    <div id="res_<?= $cur_contents['internal_id'] ?>_t" <?php if (strlen($cur_contents['title']) > 90) { ?> title="<?= $cur_contents['title'] ?>" <?php } ?>>
                        <?= Yii::$app->bipstring->shortenString($cur_contents['title'], 90) ?>
                        <?= Html::a('<i class="fa fa-info-circle" aria-hidden="true"></i>', Url::to(['site/details', 'id' => $cur_contents['doi']]), ['class' => 'grey-link', 'id' => 'a_res_' . $cur_contents['internal_id'], 'title' => 'Show details', 'target' => '_blank']); ?>
                    </div>

                    <div class="year-venue-bookmarks" id="res_<?= $cur_contents['internal_id'] ?>_jy">
                        <!-- venue -->
                        <span id="res_<?= $cur_contents['internal_id'] ?>_j" <?php if (strlen($cur_contents['journal']) > 60) { ?> title="<?= $cur_contents['journal'] ?>" <?php } ?>>
                            <?= (trim($cur_contents['journal']) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($cur_contents['journal'], 60)?>
                        </span>&middot;
                        <!-- year -->
                        <span id="res_<?= $cur_contents['internal_id'] ?>_y">
                            <?= ($cur_contents['year'] == 0) ? "N/A" : $cur_contents['year'] ?>
                        </span>
                    </div>
                </td>
                <td class="col-xs-1" style = "padding-top: 12px;">
                    <select class="reading-status" data-color = "<?= $cur_contents['reading_status']?>">
                        <option value="0" <?= ($cur_contents['reading_status'] == 0) ? "selected" : "" ?>>To Read</option>
                        <option value="1" <?= ($cur_contents['reading_status'] == 1) ? "selected" : "" ?>>Reading</option>
                        <option value="2" <?= ($cur_contents['reading_status'] == 2) ? "selected" : "" ?>>Read</option>
                    </select>
                </td>
                <td class="col-xs-1">
                <form div class='col-xs-2' id='comm-form' method='post' action='<?=Url::to(['site/movefolder'])?>' title='Move this article'>
                    <input type='hidden' name='bookmark_id' id='bookmark_id' value="<?=$cur_contents['id']?>"/>
                    <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
                    <button type="submit" class="btn btn-default btn-sm">
                        <i class="fa fa-arrows" aria-hidden="true"></i>
                    </button>
                </form>
                </td>
                <!-- impact -->
                <td class="col-xs-1">
                    <?= ImpactIcons::widget(['popularity_class' => $cur_contents['pop_class'],
                                        'influence_class' => $cur_contents['inf_class'],
                                        'impulse_class' => $cur_contents['imp_class']]);?>
                </td>
                <!-- bookmark -->
                <td class="col-xs-1" style="text-align: right; width: 1%">
                    <?= BookmarkIcon::widget(['user_liked' => True,
                                            'user_logged' => True,
                                            'id_bookmark' => $cur_contents['internal_id']]);?>

                    <!-- <?= Html::a('<i class="fa fa-info-circle" aria-hidden="true"></i>', Url::to(['site/details', 'id' => $cur_contents['doi']]), ['class' => 'my-btn', 'id' => 'a_res_' . $cur_contents['internal_id'], 'title' => 'Show details', 'target' => '_blank']); ?> -->

                    <!-- <?= Html::a('<i class="fa fa-external-link-square" aria-hidden="true"></i>', Url::to('https://doi.org/' . $cur_contents['doi']), ['class' => 'my-btn', 'id' => 'a_res_' . $cur_contents['internal_id'], 'title' => 'Show article', 'target' => '_blank']); ?> -->
                </td>
            </tr>
        <?php } ?>

        </tbody>
    </table>
<?php } ?>

<?php
    $footer = '
    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
    <button type="button" id="deletefolder" class="btn btn-danger ">Delete</button>
    ';

    Modal::begin(['options' => ['class' => 'modal fade', 'id' => 'confirm-delete-folder'],
                    'size' => '',
                    'closeButton' => False,
                    'footer' => $footer
                ]);
    echo "Are you sure you want to delete
    <span id='modaldeleteContent'></span> ?";
    Modal::end();
?>