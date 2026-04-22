<?php
/**
 * Blog post view — panel + help-text (same patterns as site/about, site/data).
 */
/* @var $this yii\web\View */
/* @var $post app\models\BlogPost */
/* @var $dataProvider \yii\data\ActiveDataProvider */

use akiraz2\blog\Module;
use app\components\BlogContentSanitizer;
use Yii;
use yii\helpers\Html;

\akiraz2\blog\assets\AppAsset::register($this);

$this->title = $post->title;
Yii::$app->view->registerMetaTag([
    'name' => 'description',
    'content' => mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->content))), 0, 160),
]);
Yii::$app->view->registerMetaTag([
    'name' => 'keywords',
    'content' => $this->title,
]);

if (Yii::$app->get('opengraph', false)) {
    Yii::$app->opengraph->set([
        'title' => $this->title,
        'description' => mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->content))), 0, 160),
    ]);
}

$post_user = $post->user;
$username_attribute = Module::getInstance()->userName;
$authorName = ($post_user && isset($post_user->{$username_attribute})) ? $post_user->{$username_attribute} : 'Unknown';
$updatedTimestamp = strtotime((string) $post->updated_at);
$createdTimestamp = strtotime((string) $post->created_at);
$updatedIso = $updatedTimestamp ? date(DATE_ATOM, $updatedTimestamp) : '';
$createdIso = $createdTimestamp ? date(DATE_ATOM, $createdTimestamp) : '';
?>
<div class="container">
    <?php if (Yii::$app->session->hasFlash('success')) : ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <article class="panel panel-default" itemscope itemtype="http://schema.org/Article">
        <meta itemprop="author" content="<?= Html::encode($authorName); ?>">
        <meta itemprop="dateModified" content="<?= Html::encode($updatedIso) ?>"/>
        <meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="<?= $post->getAbsoluteUrl(); ?>"/>
        <?php if ($post->module->enableComments) : ?>
        <meta itemprop="commentCount" content="<?= $dataProvider->getTotalCount(); ?>">
        <meta itemprop="discussionUrl" content="<?= $post->getAbsoluteUrl(); ?>">
        <?php endif; ?>
        <meta itemprop="inLanguage" content="<?= Yii::$app->language; ?>">

        <div class="panel-body">
            <div class="flex-wrap items-center justify-between" style="margin-bottom: 12px;">
                <div class="text-muted-settings">
                    <time title="<?= Module::t('blog', 'Create Time'); ?>" itemprop="datePublished"
                          datetime="<?= Html::encode($createdIso) ?>">
                        <i class="fa fa-calendar-alt"></i> <?= Yii::$app->formatter->asDate($post->created_at); ?>
                    </time>
                    <span style="margin-left: 12px;" title="<?= Module::t('blog', 'Click'); ?>">
                        <i class="fa fa-eye"></i> <?= $post->click; ?>
                    </span>
                    <?php if ($post->tagLinks) : ?>
                        <span style="margin-left: 12px;" title="<?= Module::t('blog', 'Tags'); ?>">
                            <i class="fa fa-tag"></i> <?= implode(', ', $post->tagLinks); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (! Yii::$app->user->isGuest && Yii::$app->user->identity->is_admin) : ?>
                    <div>
                        <?= Html::a(
    '<i class="fa-solid fa-pen"></i> Edit post',
    ['/blog/default/update', 'id' => $post->id],
    ['class' => 'btn btn-default btn-sm']
) ?>
                        <?= Html::a(
                            '<i class="fa-solid fa-trash"></i> Delete post',
                            ['/blog/default/delete', 'id' => $post->id],
                            [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => 'Delete this post permanently?',
                                ],
                            ]
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>

            <h1 itemprop="headline"><?= Html::encode($post->title); ?></h1>
            <?php if (! empty($post->coverImageUrl)) : ?>
                <div class="blog-view-cover-wrap">
                    <img src="<?= Html::encode($post->coverImageUrl) ?>" alt="<?= Html::encode($post->title) ?>" class="img-responsive blog-view-cover-image">
                </div>
            <?php endif; ?>
            <hr>

            <div class="help-text blog-post-content" itemprop="articleBody">
                <?= BlogContentSanitizer::purify($post->content); ?>
            </div>
            <p class="text-muted-settings blog-view-author-byline">
                <?= Html::encode($post->authorByline) ?>
            </p>

            <?php if (isset($post->module->schemaOrg) && isset($post->module->schemaOrg['publisher'])) : ?>
                <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" style="margin-top: 20px;">
                    <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
                        <meta itemprop="url image" content="<?= Yii::$app->urlManager->createAbsoluteUrl($post->module->schemaOrg['publisher']['logo']); ?>"/>
                        <meta itemprop="width" content="<?= $post->module->schemaOrg['publisher']['logoWidth']; ?>">
                        <meta itemprop="height" content="<?= $post->module->schemaOrg['publisher']['logoHeight']; ?>">
                    </div>
                    <meta itemprop="name" content="<?= $post->module->schemaOrg['publisher']['name'] ?>">
                    <meta itemprop="telephone" content="<?= $post->module->schemaOrg['publisher']['phone']; ?>">
                    <meta itemprop="address" content="<?= $post->module->schemaOrg['publisher']['address']; ?>">
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>
<?php if ($post->module->enableComments) : ?>
    <div class="container">
        <section id="comments" class="blog-comments">
            <h2 class="blog-comments__header title title--2"><?= Module::t('blog', 'Comments'); ?></h2>

            <div class="row">
                <div class="col-md-6">
                    <?= \yii\widgets\ListView::widget([
                        'dataProvider' => $dataProvider,
                        'itemView' => '_comment',
                        'viewParams' => [
                            'post' => $post,
                        ],
                    ]) ?>
                </div>
                <div class="col-md-5 col-md-offset-1">
                    <h3><?= Module::t('blog', 'Write comments'); ?></h3>
                    <?= $this->render('_form', [
                        'model' => $comment,
                    ]); ?>
                </div>
            </div>
        </section>
    </div>
<?php endif; ?>
