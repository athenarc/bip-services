<?php

use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use app\components\ScholarSidebar;
use app\components\BookmarkedPaper;

$this->title = 'BIP! Scholar - User bookmarks';
$this->registerJsFile('@web/js/third-party/bootstrap-tagsinput/bootstrap-tagsinput.min.js', ['position' => View::POS_END]);
$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/deleteFolder.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/reading-status.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/favoriteTags.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceModal.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/favorites.css');
$this->registerCssFile('@web/css/full-screen.css');
?>

<script>
    $(document).ready(function () {

        // green background for selected folder
        let id = $('div.folder-handle').attr("data-folder");
        $("#"+id).closest('li').addClass('active');
    });
</script>

<!-- Comparison bar -->
<div class="jumbotron" style="padding: 25px; padding-top: 26px; margin-bottom: 0px;">

    <a href='<?=Url::to(['site/comparison'])?>' target='_blank' id='comparison' class='btn btn-warning'></a>
    <div id='clear-comparison' onclick="clearSelected();">
        Clear all
        <i class="fa fa-times" aria-hidden="true"></i>
    </div>
</div>


<!-- Flex container -->
<div class="flex-container-fav" >

    <?= ScholarSidebar::widget(['folders' => $folders, 'highlight_key' => $highlight_key]); ?>

    <!-- Content holder -->
    <div class="folder-content" >

        <div class=folder-content-width >

            <!-- Starter page -->
            <?php if (!isset($folder_id)) :  ?>

                <!-- no bookmarked papers -->
                <?php if ($user_likes_num == 0) : ?>
                    <div class="alert alert-warning text-center margin-auto" style="width:50%; min-width: 70px;">
                        You have no bookmarks yet!
                        <i class="fa fa-question-circle" aria-hidden="true" title="To bookmark papers, click on the bookmark-shaped button of a paper in your search results."></i>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center margin-auto" style="width:50%; min-width: 70px;">
                        Select a folder
                    </div>
                <?php endif ?>

            <?php else: ?>
                <div class='folder-handle' data-folder = <?= $folder_id ?> >
                    <h4 style="display: inline-block;">
                        <b><?= $folder_info['name'] ?></b>
                    </h4>

                    <?php if ($folder_id != 'null') { ?>
                        <span style="white-space:nowrap;">
                            (<span class = "folder-articles" id = "fa_<?= $folder_id ?>" >
                                <?php
                                $folder_articles = $folder_info["num_articles"];
                                $folder_read = $folder_info["total_read"];
                                echo $folder_articles.(($folder_articles != 1) ? " articles" : " article");
                                echo empty($folder_articles) ? "" : ' - '.round(100*($folder_read/$folder_articles),0). '% read';
                                ?>
                            </span>)
                        </span>

                        <form class='small-btn-form' id='comm-form' method='post' action='<?=Url::to(['scholar/editfolder'])?>' style="display: inline-block;">
                            <input type="hidden" name="folder_id" id="edit_folder_id" value="<?= $folder_id ?>" />
                            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
                            <button class="btn bookmark-edit-button" type="submit" title="Edit folder">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                            </button>
                        </form>
                        <form class='small-btn-form' id='formfield' method='post' action='<?=Url::to(['scholar/removefolder'])?>' style="display: inline-block;">
                            <input type="hidden" name="folder_id" id="delete_folder_id" value="<?= $folder_id ?>" />
                            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
                            <button class="btn bookmark-delete-button" type="button" title="Delete folder" data-folder ="<?= $folder_info['name']?>" >
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    <?php } ?>
                </div>

                <?php if(empty($folder_contents)) : ?>
                    <p>No bookmarks in this folder</p>

                <?php else: ?>
                    <table class="table table-hover" data-folderid = "<?= $folder_id?>">
                        <tbody>
                            <?php foreach($folder_contents as $cur_contents) {
                                    echo BookmarkedPaper::widget([
                                        "internal_id" => $cur_contents["internal_id"],
                                        "bookmark_id" => $cur_contents["id"],
                                        "doi" => $cur_contents["doi"],
                                        "title" => $cur_contents["title"],
                                        "authors" => $cur_contents["authors"],
                                        "journal" => $cur_contents["journal"],
                                        "year" => $cur_contents["year"],
                                        "reading_status" => $cur_contents["reading_status"],
                                        "tags" => $cur_contents["tags"],
                                        "pop_class" => $cur_contents["pop_class"],
                                        "inf_class" => $cur_contents["inf_class"],
                                        "imp_class" => $cur_contents["imp_class"],
                                        "show" => [
                                            "tags" => true, 
                                            "involvement" => false,
                                            "reading_status" => true,
                                            "notes" => true,
                                            "move" => true,
                                            "impact_icons" => true, 
                                            "bookmark_icon" => true,
                                            "citations" => false,
                                        ]
                                    ]);
                            } ?>

                        </tbody>
                    </table>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>
</div>


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


<?php
    Modal::begin(['options' => ['class' => 'modal fade', 'id' => 'text-editor-modal'],
                    'size' => 'modal-lg',
                    'closeButton' => False,
                    'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE]
                ]);

    echo '
        <span id="loading-notes-message" style = "display:none;">
            <center><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br/><br/>
            Loading (it may take a couple of seconds)...</center>
        </span> ';
    Modal::end();
?>