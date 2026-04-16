<?php
/**
 * Blog index (views under site/blog, same convention as site/admin).
 */

use akiraz2\blog\Module;
use Yii;
use yii\helpers\Html;
use yii\widgets\ListView;

\akiraz2\blog\assets\AppAsset::register($this);

$this->title = Module::t('blog', 'Blog');
$activeTag = trim((string) Yii::$app->request->get('tag', ''));
$displayTag = $activeTag !== '' ? ucfirst($activeTag) : '';
$headerTitle = $displayTag !== '' ? Module::t('blog', 'Blog') . '/' . $displayTag : Module::t('blog', 'Blog');
Yii::$app->view->registerMetaTag([
    'name' => 'description',
    'content' => Yii::$app->name . ' ' . Module::t('blog', 'Blog'),
]);
Yii::$app->view->registerMetaTag([
    'name' => 'keywords',
    'content' => Yii::$app->name . ', ' . Module::t('blog', 'Blog'),
]);

if (Yii::$app->get('opengraph', false)) {
    Yii::$app->opengraph->set([
        'title' => $this->title,
        'description' => Module::t('blog', 'Blog'),
    ]);
}

?>
<div class="blog-index">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-sm-7 col-xs-12">
                <h1><?= Html::encode($headerTitle); ?></h1>
            </div>
            <?php if (! Yii::$app->user->isGuest && Yii::$app->user->identity->is_admin) : ?>
            <div class="col-md-4 col-sm-5 col-xs-12 blog-index-actions-col">
                <div class="text-right">
                    <?= Html::a(
                        '<i class="fa-solid fa-plus"></i> Create post',
                        ['/blog/default/create'],
                        ['class' => 'btn btn-custom-color btn-sm']
                    ) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <hr>

        <?php if (Yii::$app->session->hasFlash('success')) : ?>
            <div class="alert alert-success">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-12">
                <?php
                echo ListView::widget([
                    'dataProvider' => $dataProvider,
                    'itemView' => '_brief',
                    'options' => [
                        'class' => 'list-view blog-post-list',
                    ],
                    'itemOptions' => [
                        'class' => 'col-md-4 col-sm-6 col-xs-12 blog-index-item',
                    ],
                    'layout' => '<div class="row">{items}</div>{pager}{summary}',
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
