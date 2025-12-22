<?php

use app\components\BookmarkIcon;
use app\components\ImpactIcons;
use yii\helpers\Html;
use yii\helpers\Url;

$item = $this->context;

?>

<div id="res_<?= $item->internal_id ?>" class="minimal-view-item">
    <div class="row">
        <!-- title and basic info -->
        <div class="col-md-9">
            <?php if (isset($item->show['bookmark']) && $item->show['bookmark'] &&
                            (! isset($item->edit_perm) || (isset($item->edit_perm) && $item->edit_perm))): ?>
                <!-- bookmark -->
                <span class="bookmark-item" style="cursor: pointer;">
                <?= BookmarkIcon::widget([
                        'user_liked' => $item->user_id,
                        'user_logged' => Yii::$app->user->id,
                        'id_bookmark' => $item->internal_id
                    ]);
                ?>
                </span>
            <?php endif; ?>
            <?php
                $params = ['id' => $item->doi];

                if (isset($item) && isset($item->space_url_suffix)) {
                    $params['space_url_suffix'] = $item->space_url_suffix;
                }
                $url = Url::to(array_merge(['site/details'], $params));
            ?>
            <?= Html::a(
                Yii::$app->bipstring->lowerize(Yii::$app->bipstring->shortenString($item->title, 100)) . ' <small><i class="fa fa-info-circle" aria-hidden="true"></i></small>',
                $url,
                ['class' => 'main-green', 'title' => 'Show details', 'target' => '_blank']
            ); ?>
            <?php if (! empty($item->retracted)): ?>
                <i class="retraction-alert fa fa-exclamation-triangle" title="This article has been retracted"></i>
            <?php endif; ?>
            
            <div class="minimal-meta grey-text small">
                <!-- authors -->
                <span <?php if (strlen($item->authors) > 60) { ?> title="<?= $item->authors ?>" <?php } ?>>
                    <?= (trim($item->authors) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($item->authors, 60) ?>
                </span>&middot;
                
                <!-- venue -->
                <span <?php if (strlen($item->journal) > 40) { ?> title="<?= $item->journal ?>" <?php } ?>>
                    <?= (trim($item->journal) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($item->journal, 40) ?>
                </span>&middot;
                
                <!-- year -->
                <span><?= empty($item->year) ? 'N/A' : $item->year ?></span>
                
                <!-- openness -->
                <span class="openness-minimal">
                    <i class="fa-solid <?= Yii::$app->params['openness'][$item->is_oa]['icon_class'] ?>" aria-hidden="true" title="<?= Yii::$app->params['openness'][$item->is_oa]['name'] ?>"></i>
                </span>
            </div>
        </div>

        <!-- impact indicators -->
        <div class="col-md-3 text-right">
            <div class="minimal-impact">
                <?php if (! empty($item->dois_num) && $item->dois_num > 1): ?>
                    <?php $versionsLabel = $item->dois_num . ' ' . ($item->dois_num == 1 ? 'version' : 'versions'); ?>
                    <a href="<?= Url::to(['site/get-versions', 'openaire_id' => $item->openaire_id]) ?>"
                       modal-title="<i class=&quot;fas fa-clone&quot; aria-hidden=&quot;true&quot;></i> Other versions"
                       data-remote="false"
                       data-toggle="modal"
                       data-target="#versions-modal"
                       class="grey-link versions-minimal small version-link"
                       title="<?= Html::encode($versionsLabel) ?>">
                        <i class="fas fa-clone" aria-hidden="true"></i> <?= $item->dois_num ?>
                    </a>
                <?php endif; ?>
                
                <?= ImpactIcons::widget(['popularity_class' => $item->pop_class,
                                    'influence_class' => $item->inf_class,
                                    'impulse_class' => $item->imp_class,
                                    'cc_class' => $item->cc_class,
                                    'popularity_score' => $item->pop_score,
                                    'influence_score' => $item->inf_score,
                                    'impulse_score' => $item->imp_score,
                                    'cc_score' => $item->cc_score,
                                    'impact_indicators' => $item->impact_indicators,
                                    ]);?>
            </div>
        </div>
    </div>
</div>

