<?php

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'BIP! Services - Admin';
// $this->registerCssFile('@web/css/home.css');

$section_overview = ($section === "overview");
$section_spaces = ($section === "spaces");
$section_scholar = ($section === "scholar");
$section_indicators = ($section === "indicators");
$section_profiles = ($section === "profiles");

?>

<ul class="nav nav-tabs green-nav-tabs" style = "margin-bottom: 30px;">
    <li class="<?= $section_overview == "overview" ? 'active' : ''?>">
    <a class="" <?= !$section_overview ? "href=" . Url::to(['site/admin-overview']) : "" ?>>Overview</a>
    </li>
    <li class="<?= $section_spaces ? 'active' : ''?>">
    <a class="" <?= !$section_spaces ? "href=" . Url::to(['site/admin-spaces']) : "" ?>>Spaces</a>
    </li>
    <li class="<?= $section_indicators ? 'active' : ''?>">
    <a class="" <?= !$section_indicators ? "href=" . Url::to(['site/admin-indicators']) : "" ?>>Indicators</a>
    </li>
    <li class="<?= $section_profiles ? 'active' : ''?>">
    <a class="" <?= !$section_profiles ? "href=" . Url::to(['site/admin-profiles']) : "" ?>>Profile Templates</a>
    </li>
</ul>


<?php if ($section_overview) : ?>
    <?= $this->render('_overview', $overview_data) ?>

<?php elseif ($section_spaces) : ?>
    <?= $this->render('_spaces', $spaces_data) ?>

<?php elseif ($section_indicators) : ?>
    <?= $this->render('_indicators', $indicators_data) ?>

<?php elseif ($section_profiles) : ?>
    <?= $this->render('_profiles', $profiles_data) ?>

<?php endif ?>