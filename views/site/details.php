<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use app\components\CustomBootstrapModal;
use bigpaulie\social\share\Share;
use asu\tagcloud\TagCloud;
use app\components\BookmarkIcon;
use app\components\ImpactIcons;
use app\components\ConceptPopover;

$this->title = 'BIP! Finder - ' . $article->title;

// polar area chart with ChartJS
$this->registerJsFile('@web/js/third-party/chartjs/chart_v4.2.0.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/chartjs_polar_area.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/tinycolor.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerJsFile('@web/js/getPDFLink.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/details.css');
$this->registerCssFile('@web/css/tags.css');
$this->registerCssFile('@web/css/reading-status.css');
$this->registerJsFile('@web/js/reading-status.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$show_overall_chart = (count($article->chart_data) > 0);

$topic_chart_data = array_slice($article->chart_data, 1);
$show_topic_charts = (count($topic_chart_data) > 0);

$active_radar_chart = "active";
$active_readers_panel = (empty($active_radar_chart)) ? "active" : "";
$in_space = ($space_model->url_suffix !== null && $space_model->url_suffix !== '');
if ($in_space) {
    $spaceColor = $space_model->theme_color;
    $this->registerJs("var spaceColor = '{$spaceColor}';", View::POS_HEAD);
    $this->registerJsFile('@web/js/set_space_colors.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
}
?>

<div class='row'>
    <div class='col-xs-12'>
        <div class='article-header'>
                <div id="flex-parent">
                    <div id="floating-title">
                        <?php if (!empty($article->year)) { ?>
                            <span class="article-header-year"><?= $article->year ?> &bull;</span>
                        <?php } ?>
                        <?= $article->title ?>
                        <?php if(!empty($article['retracted'])): ?>
                            <i class="retraction-alert fa fa-exclamation-triangle"></i>
                            <span class="retraction-alert-msg">This article has been retracted</span>
                        <?php endif; ?>
                    </div>
                    <div id="floating-heart">
                        <!-- bookmark -->
                        <div class = "reading-status-details-div" data-paperid = "<?=$article->internal_id ?>" >

                            <?php
                                // $liked == null -> user not logged in or paper not liked
                                echo Html::dropDownList("res_" . $article->internal_id . "_reading-status", $article_reading_status, Yii::$app->params['reading_fields'], [
                                    'class' => "reading-status reading-status-color",
                                    'id' => 'detailsReading',
                                    'style' =>  ['visibility' => $liked != null ? "visible" : "hidden"],
                                    'data-color'=> $article_reading_status,
                                ]);
                            ?>
                        </div>
                        <div style= "display:inline-block;">
                            <?php /* $liked is  boolean
                                    false != null returns false in php */ ?>

                            <?= BookmarkIcon::widget(['user_liked' => $liked,
                                                    'user_logged' => Yii::$app->user->id,
                                                    'id_bookmark' => $article->internal_id]);?>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
<div class='row'>
    <div class='col-md-7 col-xs-12'>
        <div class='article-info'>
            <b><?= $article->getAttributeLabel('authors') ?>:</b> <?= $article->authors ?>
        </div>
        <?php if (!empty($article->language)) : ?>
            <div class='article-info'>
                <b><?= $article->getAttributeLabel('language') ?>:</b> <?= $article->language ?>
            </div>
        <?php endif; ?>
        <div class='article-info'>
            <b><?= $article->getAttributeLabel('journal') ?>:</b> <?= empty(trim($article->journal)) ? "N/A" : $article->journal ?>
        </div>
        <div class='article-info'>
            <b><?= $article->getAttributeLabel('type') ?>:</b> <i class="fa-solid <?= Yii::$app->params['work_types'][$article->type]['icon_class'] ?>" aria-hidden="true" title = "<?= Yii::$app->params['work_types'][$article->type]['title'] ?>"></i> <?= Yii::$app->params['work_types'][$article->type]['name'] ?>
        </div>
        <div class='article-info'>
            <b><?= $article->getAttributeLabel('abstract') ?>:</b> <?= $article->abstract ?>
        </div> 

        <!--Impact-->
        <?= ImpactIcons::widget([
            'popularity_class'   => $article->pop_class,
            'influence_class'    => $article->inf_class,
            'impulse_class'      => $article->imp_class,
            'cc_class'           => $article->cc_class,
            'popularity_score'   => $article->attrank,
            'influence_score'    => $article->pagerank,
            'impulse_score'      => $article->{'3y_cc'},
            'cc_score'           => $article->citation_count,
            'impact_indicators'  => Yii::$app->params['impact_indicators'],
            'options' => ['showScoreLabel' => true],
            'num_likes' => $article->getNumLikes(),
            'num_views' => $article->getGuestViews() + $article->getUserViews(),
        ]) ?>
        

        <div class='article-info tag-region'>

            <div class="bootstrap-tagsinput">
                <b>Topics:&nbsp</b>

                <?php if (empty($article->concepts)): ?>
                    N/A
                <?php else: ?>
                    <?php foreach($article->concepts as $concept):?>
                        <span class="tag label">
                            <?php $data_content = ConceptPopover::widget(['concept' => $concept]);?>
                            <span role="button" data-toggle="popover" data-placement="auto" title="<b><?= $concept['display_name'] ?></b>" data-content="<?= $data_content ?>"><?= $concept['display_name'] ?></span><span class= "concept-confidence" title = "Confidence: <?= round($concept['concept_score'],2) ?>" ><i class="fa-concept-confidence fa-solid fa-circle" style = "background-image: linear-gradient(to right, var(--main-color) <?= 100*round($concept['concept_score'],2) ?>%, #ddd 0%);"></i></span>
                        </span>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </div>
        <div class='article-info'>
            <b><?= $article->getPidName() ?>:</b>
            <?php if(empty($article->doi)){
                    echo "N/A";
                } else if (!empty($article->doi)) { ?>
                    <?php if ($article->getPidName() === 'DOI') :?>
                        <a href="https://doi.org/<?= $article->doi?>" target='_blank' class="main-green"><?= $article->doi ?> <i class="fa fa-external-link-square" aria-hidden="true"></i></a>
                    <?php elseif ($article->getPidName() === 'PubMed Id') :?>
                        <a href="https://www.ncbi.nlm.nih.gov/pubmed/<?= $article->doi?>" target='_blank' class="main-green"><?= $article->doi ?> <i class="fa fa-external-link-square" aria-hidden="true"></i></a>
                    <?php endif; ?>
            <?php } ?>
        </div>
        <div class='row'>
            <div class='col-xs-12'>
                <div class='article-info'>
                    
                    <!-- <b><?= $article->getAttributeLabel('abstract_score') ?> <i class="fa fa-question-circle" aria-hidden="true" title="Based on the Flesch Reading Ease metric calculated on abstracts"></i>:</b> <?= (empty($article->abstract_score)) ? 'N/A' : $article->abstract_score ?><br/> -->

                    <b>External links:</b>
                    <?php if(!empty($article->doi) && $article->getPidName() === 'DOI'){ ?>
                        <a href="https://search.crossref.org/search/works?q=<?= $article->doi ?>&from_ui=yes" target='_blank' class="main-green">Crossref <i class="fa fa-external-link-square" aria-hidden="true"></i></a>
                    <?php } ?>


                    <?php if (!empty($article->doi)) { ?>
                        <a href="https://explore.openaire.eu/search/advanced/research-outcomes?f0=pid&fv0=<?= $article->doi?>" target='_blank' class="main-green">OpenAIRE <i class="fa fa-external-link-square" aria-hidden="true"></i></a>
                    <?php } ?>

                    <!-- Similar Articles -->
                        <!-- <a href="<?= Url::to(['site/get-similar-articles', 'paper_id' => $article->internal_id]) ?>" modal-title="Similar Articles" data-remote="false" data-toggle="modal" data-target="#similar-modal" class="btn btn-sm btn-custom-color">
                                <i class="fa fa-search"></i> Similar Articles
                        </a> -->
                    <!-- altmetric badge -->
                    <?php if(!empty($article->doi)){ ?>
                        <!-- <div data-badge-popover="right" data-badge-type="1" data-doi="<?= $article->doi?>" data-hide-no-mentions="true" class="altmetric-embed"></div> -->
                    <?php } ?>
                </div>
            </div>
            <?php if (!empty($article->relations)): ?>
                <div class='col-xs-12'>
                    <!-- relations -->
                    <div class='article-info tag-region'>
                        <div class="bootstrap-tagsinput">
                        <b>Related works:&nbsp</b>

                        <?php foreach ($article->relations as $relation) { ?>
                            <span class="tag label">
                                <span role="button" href="<?= Url::to(['site/get-relations-data', 'target_dois' => $relation['target_dois'], 'source_openaire_id' => $article->openaire_id]) ?>" data-toggle="modal" data-remote="false" modal-title="Related works" data-target="#relations-modal"><?= $relation['type'] ?> <span class="badge badge-primary" style ="top: -1px;padding: 1px 5px; position: relative;"><?= count($relation['target_dois'])?></span></span>
                                
                            </span>
                        <?php } ?>

                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class='col-xs-12'>
                <div class='article-buttons'>
                    
                    <?php if(!empty($article->dois_num) && $article->dois_num > 1): ?>
                        <a href="<?= Url::to(['site/get-versions', 'openaire_id' => $article->openaire_id]) ?>" modal-title="<i class=&quot;fas fa-clone&quot; aria-hidden=&quot;true&quot;></i> Other versions" data-remote="false" data-toggle="modal" data-target="#versions-modal" class="btn btn-sm btn-custom-color">
                            <i class="fas fa-clone"></i> Found <?= $article->dois_num ?> versions
                        </a>
                    <?php endif; ?>

                    <!-- <a href="<?= Url::to(['site/get-references', 'paper_id' => $article->internal_id]) ?>" modal-title="<i class=&quot;fas fa-up-right-and-down-left-from-center&quot; aria-hidden=&quot;true&quot;></i> References" data-remote="false" data-toggle="modal" data-target="#references-modal" class="btn btn-sm btn-custom-color <?= ($article->references_count == 0) ? "disabled" : "" ?>">
                            <i class="fas fa-up-right-and-down-left-from-center" aria-hidden="true"></i> References (<?= $article->references_count ?>)
                    </a>
                    <a href="<?= Url::to(['site/get-citations', 'paper_id' => $article->internal_id]) ?>" modal-title="<i class=&quot;fa-solid fa-down-left-and-up-right-to-center&quot; aria-hidden=&quot;true&quot;></i> Citations (<?= $article->citation_count ?>)" data-remote="false" data-toggle="modal" data-target="#citations-modal" class="btn btn-sm btn-custom-color  <?= ($article->citation_count == 0) ? "disabled" : "" ?>">
                                <i class="fa-solid fa-down-left-and-up-right-to-center" aria-hidden="true"></i> Citations (<?= $article->citation_count ?>)
                    </a> -->
                    <a href="<?= Url::to(['site/download-bibtex', 'doi' => $article->doi]) ?>" modal-title="<i class=&quot;fas fa-quote-right&quot; aria-hidden=&quot;true&quot;></i> BibTex" data-remote="false" data-toggle="modal" data-target="#bibtex-modal" class="btn btn-sm btn-custom-color <?= $article->getPidName() === 'DOI' ? '' : 'disabled' ?>">
                            <i class="fas fa-quote-right" aria-hidden="true"></i> BibTex
                    </a>
                    <a id="pdf_button" href="#" class="btn btn-sm btn-custom-color disabled" target='_blank' onclick="getPDFLink('<?= Url::to(['site/get-pdf-link']) ?>', '<?= $article->doi ?>');">
                        <i class="fa fa-download" aria-hidden="true"></i> PDF
                    </a>

                </div>
            </div>
            <div class='col-xs-12'>
                <div class='article-buttons'>
                    <?= Share::widget([
                        'type' => Share::TYPE_EXTRA_SMALL,
                        'tag' => 'div',
                        'template' => '{button} ',
                        'include' => ['facebook', 'twitter', 'linkedin'],
                        'htmlOptions' => [
                            'class' => 'grey-share',
                        ],
                    ]); ?>
                </div>
            </div>

            <!-- modals for references and citations; initially they are hidden -->
            <div class="col-xs-12">
                <!-- <?= CustomBootstrapModal::widget(['id' => 'references-modal']); ?> -->
                <!-- <?= CustomBootstrapModal::widget(['id' => 'citations-modal']); ?> -->
                <?= CustomBootstrapModal::widget(['id' => 'relations-modal']); ?>
                <?= CustomBootstrapModal::widget(['id' => 'similar-modal']); ?>
                <?= CustomBootstrapModal::widget(['id' => 'bibtex-modal']); ?>
                <?= CustomBootstrapModal::widget(['id' => 'versions-modal']); ?>
            </div>
        </div>
    </div>

    <!-- Do not show pyramids for articles with no scores -->
    <?php if ($show_overall_chart) :?>
        <div class='col-md-offset-1 col-md-4 col-xs-12'>
            <!-- render chart with overall ranking/classes -->
            <div class="radar-container style="position: relative;">
                <canvas id="chart"></canvas>
            </div>
            <script>
                render_polar_area_chart('chart', 'Cross-topic impact indicators', <?= json_encode($article->chart_data['overall']['data']) ?>, <?= json_encode($article->chart_data['overall']['tooltips']) ?>, '<?= $space_model->theme_color ?>');
            </script>
        </div>
    <?php endif; ?>
</div>

<!-- add vertical space	-->
<div style="margin-top: 20px;"></div>

<?php if ($show_topic_charts == true): ?>

    <div class="row">
        <div class="col-md-12 details-container">
            <!-- 	tab header	 -->
            <ul class="nav nav-tabs nav-justified green-nav-tabs">

                <li class="<?= $active_radar_chart ?>">
                    <a data-toggle="tab" href="#radar_chart_panel">
                        <!-- Impact aspects and other metrics -->
                        Topic-specific impact indicators
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content">
        <div id="radar_chart_panel" class="tab-pane fade in <?= $active_radar_chart ?> details-container">
            <div class="row">
                <?php foreach($topic_chart_data as $key => $topic_data)  { ?>

                    <div class="radar-container col-md-<?= 12/(count($article->chart_data) - 1)?>" style="position: relative;">
                        <canvas id="chart-<?= $key ?>"></canvas>
                    </div>

                    <script>
                        render_polar_area_chart('chart-<?= $key ?>', 'Based on topic "<?= $article->concepts[$key]['display_name'] ?>"', <?= json_encode($topic_data['data']) ?>, <?= json_encode($topic_data['tooltips']) ?>, '<?= $space_model->theme_color ?>');
                    </script>
                <?php } ?>
            </div>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-custom-color text-left">
                    <small>
                        <ul id="indicators-list">
                            <li><b>Popularity:</b> <?= $indicators['Popularity'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Popularity'])?>'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></li>
                            <li><b>Influence:</b> <?= $indicators['Influence'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Influence'])?>'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></li>
                            <li><b>Citation Count:</b> <?= $indicators['Influence-alt'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Impulse'])?>'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></li>
                            <li><b>Impulse:</b> <?= $indicators['Impulse'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Impulse'])?>'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
