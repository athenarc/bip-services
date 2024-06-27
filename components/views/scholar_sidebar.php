<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\StringHelper;
$this->registerCssFile('@web/css/components/scholar-sidebar.css');
$this->registerJsFile('@web/js/components/scholar-sidebar.js', ['position' => yii\web\View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

?>

<!-- Sidebar holder -->
<nav class="sidebar-fav"  >

    <div class="sidebar-fav-header">

        <h3 style="color: unset;">
            BIP! Scholar
        </h3>

    </div>

    <ul class="sidebar-fav-components nav nav-pills nav-stacked" >
        <li class="nav-divider"></li>

        <li class=<?= $highlight_key == 'Profile' ? "active" : "" ?>>
            <?= Html::a("Profile<sup><small>beta</small></sup>", Url::to(['scholar/profile']), ['class' => 'main-menu']); ?>
        </li>
        <li class=<?= $highlight_key == 'Readings' ? "active" : "" ?>>
            <?= Html::a("Readings", Url::to(['scholar/readings']), [ 'class' => 'main-menu']); ?>
        </li>
        <li title="Not currently supported">
            <?= Html::a("Discover", Url::to(['scholar/discover']), [ 'class' => 'noclick main-menu']); ?>
        </li>
        <!-- <li style="display:flex; justify-content: space-between; align-items: baseline;" class = <?= $highlight_key == 'Bookmarks' ? "active" : "" ?> >
            <?= Html::a("Bookmarks", Url::to(['scholar/favorites']), ['class' => 'main-menu']) ?>

            <div class='create-folder'>
                <a href="<?=Url::to(['scholar/createfolder'])?>">
                    <i class="fa-solid fa-plus fa-sm" aria-hidden="true"></i>
                    Add topic
                </a>
            </div>
        </li> -->

        <!-- <?php foreach ($folders as $folder) { ?>
            <li class="bookmarks-menu">
                <?= Html::a($folder['name'], Url::to(['scholar/favorites', 'id' => StringHelper::base64UrlEncode($encoding_prefix.$folder['id'])]), ['class' => '', 'id' => $folder['id'], 'title' => '']); ?>
            </li>
        <?php } ?>
            <li class="bookmarks-menu">
                <?= Html::a("Misc. bookmarks", Url::to(['scholar/favorites', 'id' => StringHelper::base64UrlEncode($encoding_prefix.'null')]), ['class' => '', 'id' => 'null', 'title' => '']); ?>
            </li> -->
    </ul>
</nav>