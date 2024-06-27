<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\components\ImpactIcons;
use app\components\BookmarkIcon;

$this->title = 'BIP! Finder - Comparison';

$this->registerJsFile('@web/js/third-party/d3/d3.min.js',  ['position' => View::POS_HEAD]);
$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/radarChart.js',  ['position' => View::POS_HEAD]);

?>

<div class="container">
<div class="row">
    <div class="col-md-3 col-md-offset-3 vcenter">
       <?= Html::img("@web/img/bip-minimal.png", ['class' => 'img-responsive center-block','width' => 100, 'height' => 75]) ?>
    </div>
    <div class="col-md-3 vcenter">
        <h2 style="margin-top:0; margino-bottom: 0">
            Comparison
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-hover">
        <thead>
        <tr>
            <th></th>
            <th>Title</th>
            <th>Venue</th>
            <th>Year</th>
            <th>Impact</th>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php
            $i = 0;
            foreach($articles as $row )
            {
        ?>
                    <tr id="res_<?= $row['internal_id'] ?>" class="text-left">

                        <!-- color -->
                        <td>
                            <i id="dot_<?=$i?>" class="fa fa-circle" aria-hidden="true"></i></div>
                        </td>

                        <!-- title -->
                        <td id="res_<?= $row['internal_id'] ?>_t" <?php if (strlen($row['title']) > 80) { ?> title="<?= $row['title'] ?>" <?php } ?>>
                            <?= Html::a(Yii::$app->bipstring->shortenString($row['title'],80) . ' <i class="fa fa-info-circle" aria-hidden="true"></i>', Url::to(['site/details', 'id' => $row['doi']]), ['class' => 'grey-link', 'title' => 'Show details', 'target' => '_blank']); ?>
                        </td>

                        <!-- venue -->
                        <td id="res_<?= $row['internal_id'] ?>_j" class="grey-text" <?php if (strlen($row['journal']) > 25) { ?> title="<?= $row['journal'] ?>" <?php } ?>>
                            <?= (trim($row['journal']) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($row['journal'], 25) ?>
                        </td>

                        <!-- year -->
                        <td id="res_<?= $row['internal_id'] ?>_y" class="grey-text">
                            <?= $row['year'] ?>
                        </td>

                        <!-- impact -->
                        <td id="res_<?= $row['internal_id'] ?>_i">
                            <?= ImpactIcons::widget(['popularity_class' => $row['pop_class'],
                                                    'influence_class' => $row['inf_class'],
                                                    'impulse_class' => $row['imp_class'],
                                                    'cc_class' => $row['cc_class'],
                                                    'popularity_score' => $row['attrank'],
                                                    'influence_score' => $row['pagerank'],
                                                    'impulse_score' => $row['3y_cc'],
                                                    'cc_score' => $row['citation_count'],
                                                    ]);?>
                        </td>

                        <!-- bookmark -->
                        <td>
                            <?= BookmarkIcon::widget(['user_liked' => $row['user_id'],
                                                    'user_logged' => Yii::$app->user->id,
                                                    'id_bookmark' => $row['internal_id']]);?>

                        </td>

                        <td>
                            <?= Html::a('<i class="fa fa-times" aria-hidden="true"></i>', Url::to(['site/comparison']), ['class' => 'my-btn-discreet', 'title' => 'Remove from comparison', 'onclick'=>"clickRemoveBtn(".$row['internal_id'].")"]); ?>
                        </td>
                    </tr>
        <?php
                $i++;
            }
        ?>
        </tbody>
        </table>
    </div>
</div>
<!-- add vertical space	 -->
<div style="margin-top: 20px;"></div>

<div class="row">
    <div class="col-md-12 details-container">
        <!-- 	tab header	 -->
        <ul class="nav nav-tabs nav-justified green-nav-tabs">
            <li class="active"><a data-toggle="tab" href="#radar_chart">Impact aspects and other metrics</a></li>
            <!-- <li><a data-toggle="tab" href="#citations_per_year_chart">Citations per year <i class="fa fa-question-circle" aria-hidden="true" title="Based on OpenCitations"></i></a></li> -->
        </ul>
    </div>
</div>
<div id="comparison_graph"></div>

<!-- 	tab content	 -->
    <div class="tab-content">
        <div id="radar_chart" class="tab-pane fade in active details-container">
            <div class="row">
                <div class="col-md-12">
                    <div class="radar-container">
                        <div class="radarChart"></div>
                        <div class="alert alert-success text-left">
                            <ul>
                                <li><b>Popularity: <?= Yii::$app->params['Indicators']['Article-level Indicators']['Impact Indicators']['Popularity']['Intuition'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Popularity'])?>'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></b></li>
                                <li><b>Influence: <?= Yii::$app->params['Indicators']['Article-level Indicators']['Impact Indicators']['Influence']['Intuition'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Influence'])?>'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></b></li>
                                <li><b>Citation Count: <?= Yii::$app->params['Indicators']['Article-level Indicators']['Impact Indicators']['Influence-alt']['Intuition'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Impulse'])?>'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></b></li>
                                <li><b>Impulse: <?= Yii::$app->params['Indicators']['Article-level Indicators']['Impact Indicators']['Impulse']['Intuition'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Impulse'])?>'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></b></li>
                            </ul>
                        </div>
                            <script>
                                var colors = [];
                                var data = [];
                                <?php
                                    $i=0;
                                    $max_value = 0;
                                    foreach($articles as $row )
                                    {
                                        $all_values = [
                                            $row['attrank_normalized'],
                                            $row['pagerank_normalized'],
                                            $row['3y_cc_normalized']
                                         ];

                                        if( $max_value < max($all_values) )
                                            $max_value = max($all_values);
                                ?>

                                //get color
                                var element = document.getElementById('dot_<?=$i?>'),
                                style = window.getComputedStyle(element),
                                color = style.getPropertyValue('color');
                                colors.push(color);

                                //get article data
                                var article_data = [
                                    {axis:"Popularity",value:<?=$row['attrank_normalized']?>, id:<?=$i?>},
                                    {axis:"Influence",value:<?=$row['pagerank_normalized']?>, id:<?=$i?>},
                                    {axis:"Citation Count",value:<?=$row['citation_count_normalized']?>, id:<?=$i?>},
                                    {axis:"Impulse",value:<?=$row['3y_cc_normalized']?>, id:<?=$i?>},
                                ];
                                data.push(article_data);

                                <?php
                                        $i++;
                                    }
                                    if( $max_value == 0 )
                                        $max_value = 1;

                                ?>

                                total_values = {};
                                initRadarChart(colors,data,<?=$max_value?>,[],[], [],[], total_values);
                            </script>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div id="citations_per_year_chart" class="tab-pane fade details-container">
            <div class="row">
                <div class="col-md-12">
                    <div class="citation-history-container">
                        <div class="citation-line-chart">
                            <? /* php $this->registerJsFile ( '@web/js/drawLineChart.js', ['position' => \yii\web\View::POS_HEAD]) */?>
                            <script>$(document).ready(drawLineChart(<?php /* echo "$xmin,$xmax,$ymax,$citation_data" */?>))</script>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</div>

