<?php
/**
 * Blog list item — panel block (same as site/about citations).
 */

use yii\helpers\Html;

?>

<div class="panel panel-default blog-card">
    <div class="panel-body blog-card-body">
        <?php
        $contentPlain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $model->content)));
        $excerptLimit = 120;
        if (mb_strlen($contentPlain, 'UTF-8') > $excerptLimit) {
            $contentExcerpt = rtrim(mb_substr($contentPlain, 0, $excerptLimit, 'UTF-8')) . '...';
        } else {
            $contentExcerpt = $contentPlain;
        }
        ?>
        <div class="blog-card-cover-wrap">
            <?php if (! empty($model->coverImageUrl)) : ?>
                <?= Html::a(
                    Html::img(
                        $model->coverImageUrl,
                        [
                            'alt' => $model->title,
                            'class' => 'img-responsive',
                            'class' => 'img-responsive blog-card-cover-image',
                        ]
                    ),
                    $model->url,
                    ['class' => 'blog-card-cover-link', 'aria-label' => 'Open post']
                ) ?>
            <?php else : ?>
                <?= Html::a(
                    '',
                    $model->url,
                    ['class' => 'blog-card-cover-link blog-card-cover-link--empty', 'aria-label' => 'Open post']
                ) ?>
            <?php endif; ?>
        </div>
        <div class="grey-text blog-card-title-wrap">
            <b><?= Html::a(Html::encode($model->title), $model->url, ['class' => 'main-green']); ?></b>
        </div>
        <div class="text-muted-settings blog-card-excerpt">
            <?= Html::encode($contentExcerpt) ?>
        </div>
        <div class="flex-wrap items-center justify-between blog-card-meta-row">
            <span class="text-muted-settings">
                <i class="fa fa-calendar"></i> <?= Yii::$app->formatter->asDate($model->created_at); ?>
                <?php if ($model->tagLinks) : ?>
                    <span class="blog-card-tags-gap">
                        <i class="fa fa-tag"></i> <?= implode(', ', $model->tagLinks); ?>
                    </span>
                <?php endif; ?>
            </span>
            <span class="text-muted-settings">
                <i class="fa fa-eye"></i> <?= (int) $model->click; ?>
            </span>
        </div>
    </div>
</div>
