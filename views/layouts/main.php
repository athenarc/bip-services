<?php

/* @var $this \yii\web\View */
/* @var $content string */

use Yii;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\helpers\Url;
use app\components\CookieBox;
use app\components\GoogleAnalytics;
use app\components\Matomo;

AppAsset::register($this);

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-84275353-2"></script>

    <!--Register google analytics-->
    <?= GoogleAnalytics::checkAndAdd() ?>

    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="shortcut icon" href="<?= Url::to('@web/img/favicon-minimal.ico') ?>" type="image/x-icon" />

    <!-- Register matomo analytics -->
    <?= Matomo::checkAndAdd() ?>
    <?= Matomo::checkAndAddNonScript() ?>

    <?php $this->registerJs("var appBaseUrl = '" . Url::base() . "';", \yii\web\View::POS_HEAD); ?>

    <!-- Open Graph meta tags for better social media link previews -->
    <meta property="og:title" content="<?= Html::encode($this->title) ?>">
    <meta property="og:image" content="<?= Url::to('@web/img/bip-minimal.png', true) ?>">
    <meta property="og:url" content="<?= Url::to('', true) ?>">
    <meta property="og:type" content="website">

    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div id="overwrap">
    <div class="wrap">
        <?php
        NavBar::begin([
            'brandLabel' => 'BiP!',
            'brandUrl' => ['site/home'],
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-left bip-top-navbar'],
            'items' => [
                [
                    'label' => Yii::$app->session->get('selectedServiceName', 'Services'),
                    'items' => Yii::$app->params['services'],
                ]
            ],
            'encodeLabels' => false,
            'activateItems' => true,
        ]);

        $items = [
            ['label' => 'About', 'url' => ['/site/about']],
            ['label' => 'Open data', 'url' => ['site/data']],
            ['label' => 'Indicators', 'url' => ['site/indicators']],
            ['label' => 'Help', 'url' => ['/site/help']],
            [
                'label' => 'Admin', 'url' => ['/site/admin-overview'], 
                'active' => in_array(Yii::$app->controller->action->getUniqueId() , ["site/admin-overview", "site/admin-spaces"]),
                'visible' => Yii::$app->user->isGuest ? False : Yii::$app->user->identity->is_admin
            ]
        ];

        if(Yii::$app->user->isGuest)
        {
           array_push($items,
                ['label' => 'Log In', 'url' => ['site/login']]
            );
        }
        else
        {
            $item = [
                'label' => '<i class="fa-solid fa-user"></i> ' . Yii::$app->user->identity->username,
                'items' => [
                    ['label' => '<i class="fa-solid fa-gears"></i> ' . 'Settings', 'url' => [ 'site/settings' ]],
                    ['label' => '<i class="fa-solid fa-paper-plane"></i> ' . 'Contact & Feedback', 'url' => ['/site/feedback']],
                    ['label' => '<i class="fa-solid fa-right-from-bracket"></i> ' . 'Logout', 'url' => ['site/logout'], 'linkOptions' => ['data-method' => 'post']]
                ]
            ];
            array_push($items, $item);
        }

        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right bip-top-navbar'],
            'items' => $items,
            'encodeLabels' => false,
        ]);
        NavBar::end();
        ?>

        <div class="container">
            <!-- <div class="panel panel-danger text-center">
                <div class="panel-heading">
                    <p> BIP FINDER IS CURRENTLY UNDER MAINTENACE!
                    <br/>
                </div>
            </div> -->

            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= $content ?>
        </div>
    </div>

    <?=CookieBox::show()?>
</div>

<?php
// Global modals
require_once(Yii::$app->basePath . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "layouts" . DIRECTORY_SEPARATOR . "delete_bookmark_modal.php");
// require_once(Yii::$app->basePath . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "layouts" . DIRECTORY_SEPARATOR . "mobile_warning_modal.php");
?>

<footer class="footer-new">

    <div class="container">
        <div class="footer-flex">
            <div>
                <a href="http://www.imis.athena-innovation.gr/" target="_blank">
                    <?= Html::img("@web/img/athena_rc.png", ['class' => 'logo-footer img-responsive' , 'style' => "max-height: 30px" , 'alt' => 'Athena RC logo']) ?>
                </a>
            </div>
            <div>
                Follow us:
                <a href="https://twitter.com/BipFinder" target="_blank"><i class="fa-brands fa-twitter main-green"></i></a>
                <a rel="me" href="https://mastodon.social/@BipServices" target="_blank"><i class="fa-brands fa-mastodon main-green"></i></a>
            </div>
            <div>
                <a href="https://graph.openaire.eu/" target="_blank">
                    <?= Html::img("@web/img/openaire_badge.png", ['class' => 'logo-footer img-responsive', 'style' => "max-height: 17px",'alt' => 'Openaire logo']) ?>
                </a>
            </div>
            <div class="text-center">
                Copyright © <?= date("Y") ?>
                <?= Html::a('bip@athenarc', 'mailto: bip@athenarc.gr', [ 'class' => 'main-green' ]) ?>
                |
                <?= Html::a('Privacy Settings', Url::toRoute('site/privacy-settings'), [ 'class' => 'main-green' ]) ?>
            </div>
        </div>
    </div>
</footer>


<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
