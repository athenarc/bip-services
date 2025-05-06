<?php
use yii\helpers\Html;
use app\components\ImpactIcons;
?>

<div class="citation-scores" style="margin: 3px 0 4px 0; display: flex; align-items: center; flex-wrap: wrap;">

    <!-- Impact header -->
    <div style="font-weight: bold; font-size: 14px; font-family: 'Nunito', sans-serif; color: #808080; margin-right: 8px;">
        Impact:
    </div>

    <!-- Impact Icons widget with popovers and score labels -->
    <?= ImpactIcons::widget([
        'popularity_class'   => $pop_class,
        'influence_class'    => $inf_class,
        'impulse_class'      => $imp_class,
        'cc_class'           => $cc_class,
        'popularity_score'   => $pop,
        'influence_score'    => $inf,
        'impulse_score'      => $imp,
        'cc_score'           => $cc,
        'impact_indicators'  => Yii::$app->params['impact_indicators'],
        'options' => ['showScoreLabel' => true]
    ]) ?>
</div>