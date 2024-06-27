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
use app\components\ResultItem;

$this->title = 'BIP! Finder - ' . $article['title'];

//  polar area chart with ChartJS
$this->registerJsFile('@web/js/third-party/chartjs/chart_v4.2.0.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/chartjs_polar_area.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/readMore.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerCssFile('@web/css/details.css');
$this->registerCssFile('@web/css/missing-works.css');
$this->registerCssFile('@web/css/tags.css');

$missing_dois_num = count($article->missing_dois);

$edit_perm = isset($userid);

?>

<!-- Comparison bar -->
<span class="jumbotron">
    <a href='<?=Url::to(['site/comparison'])?>' target='_blank' id='comparison' class='btn btn-warning'></a>
    <div id='clear-comparison'  onclick="clearSelected();">
        Clear all
        <i class="fa fa-times" aria-hidden="true"></i>
    </div>
</span>

<div class='row'>
    <div class='col-xs-12'>
        <div class='article-header'>
            <div id="flex-parent">
                <div id="floating-title">
                    <?php if (!empty($article->year)) { ?>
                        <span class="article-header-year"><?= (!empty($article->year)) ? $article->year : 'N/A' ?> &bull;</span>
                    <?php } ?>
                    <?= (!empty($article->title)) ? $article->title : 'N/A' ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class='row'>
    <div class='col-md-7 col-xs-12'>
        <div class='article-info'>
            <b><?= $article->getAttributeLabel('authors') ?>:</b> <?php if (empty($article->authors)) {
                echo "N/A";
            } else { ?>
                <span class="show-value">
                    <?= Yii::$app->bipstring->shortenString($article->authors, 250) ?>
                </span>
                <?php if (strlen($article->authors) > 250) { ?>
                    <span class="hidden-value" style="display: none;">
                        <?= $article->authors ?>
                    </span>
                    <span class="toggle-btn main-green">(Read More)</span>
                <?php } ?>
            <?php } ?>
        </div>
        <div class='article-info'>
            <b><?= $article->getAttributeLabel('abstract') ?>:</b> <?php if (empty($article->abstract)) {
                echo "N/A";
            } else { ?>
                <span class="show-value">
                    <?= Yii::$app->bipstring->shortenString($article->abstract, 550) ?>
                </span>
                <?php if (strlen($article->abstract) > 550) { ?>
                    <span class="hidden-value" style="display: none;">
                        <?= $article->abstract ?>
                    </span>
                    <span class="toggle-btn main-green">(Read More)</span>
                <?php } ?>
            <?php } ?>
        </div>
        <div class='row'>
            <div class='col-xs-12'>
                <div class='article-info'>
                    <b>External links:</b>
                    <a href="https://explore.openaire.eu/search/publication?articleId=<?= $article->id ?>" target='_blank' class="main-green">OpenAIRE <i class="fa fa-external-link-square" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Do not show pyramids for articles with no scores -->
    <?php if (isset($article->measures_classes)):?>
        <div class='col-md-offset-1 col-md-4 col-xs-12'>
            <!-- render chart with overall ranking/classes -->
            <div class="radar-container" style="position: relative;">
                <canvas id="chart"></canvas>
            </div>
            <script>
                render_polar_area_chart('chart', 'Cross-topic impact indicators', <?= json_encode($article->chart_data['data']) ?>, <?= json_encode($article->chart_data['tooltips']) ?>);
            </script>

        </div>
    <?php endif; ?>
</div>

<!-- add vertical space	-->
<div style="margin-top: 20px;"></div>

<?php if (!empty($article->dois)): ?>
    <div class="row" >
        <div class="col-md-8">
            <h4 style="display: inline-block;">
                <b>Works in BIP! associated with the specified OpenAIRE research product</b>
            </h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 details-container">

            <?php foreach ($article->doi_papers as $paper) {
                echo ResultItem::widget([
                    "internal_id" => $paper["internal_id"],
                    "edit_perm" => true,
                    "user_id" => $paper["user_id"],
                    "doi" => $paper["doi"],
                    "title" => $paper["title"],
                    "authors" => $paper["authors"],
                    "journal" => $paper["journal"],
                    "year" => $paper["year"],
                    "concepts" => $paper["concepts"],
                    "tags" => isset($paper["tags"]) ? $paper["tags"] : '',
                    "notes" => isset($paper["notes"]) ? $paper["notes"] : '',
                    "involvements" => Yii::$app->params['involvement_fields'],
                    // "involved" => $paper["involvement"],
                    "pop_score" => $paper["attrank"],
                    "inf_score" => $paper["pagerank"],
                    "imp_score" => $paper["3y_cc"],
                    "cc_score" => $paper["citation_count"],
                    "pop_class" => $paper["pop_class"],
                    "inf_class" => $paper["inf_class"],
                    "imp_class" => $paper["imp_class"],
                    "cc_class" => $paper["cc_class"],
                    "is_oa" => $paper["is_oa"],
                    "type" => $paper["type"],
                    "show" => [
                        "concepts" => true,
                        "tags" => false,
                        "reading_status" => false,
                        "notes" => false,
                        "bookmark" => true,
                    ]
                ]);
            } ?>
        </div>
    </div>
<?php else: ?>
    <div class="text-center">BIP! software was not able to retrieve any works related to this OpenAIRE identifier.</div>
<?php endif; ?>

<?php if ($missing_dois_num > 0): ?>
    <div id="missing-publications-toggle" class="col-md-12 text-center">
        <button type="button" class="btn btn-link missing-publications-toggle"
        data-toggle="collapse" data-target="#missing-publications">
            <b>Missing works (<?= $missing_dois_num ?>)</b> </i>
        </button>
    </div>
    <div id="missing-publications" class="collapse">
        <div class="row" >
            <div class="col-md-8">
                <h5 style="display: inline-block;">
                    <b>Missing works <i class="fa fa-question-circle" aria-hidden="true" title="This list contains works retrieved from OpenAIRE that BIP! software do not contain in its database."></i></b>
                </h5>
            </div>
        </div>
        <table class="table table-hover">
            <tbody>
                <?php foreach ($article->missing_dois as $doi): ?>
                    <tr class="text-left ">
                        <td class="col-xs-8">
                            <a href="https://doi.org/<?= $doi?>" target='_blank' class="main-green"><?= $doi ?> <i class="fa fa-external-link-square" aria-hidden="true"></i></a>
                        <?php /*

                            <!-- title -->
                            <div <?php if (strlen($paper["title"]) > 90) { ?> title="<?= $paper['title'] ?>" <?php } ?>>
                                <?= Yii::$app->bipstring->shortenString($paper["title"], 90) ?>
                            </div>

                            <div class="year-venue-bookmarks">

                                <!-- venue -->
                                <span <?php if (isset($paper["journal"]) && strlen($paper["journal"]) > 60) { ?> title="<?= $paper['journal'] ?>" <?php } ?>>
                                    <?= (!isset($paper["journal"]) || trim($paper["journal"]) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($paper["journal"], 60)?>
                                </span>&middot;

                                <!-- year -->
                                <span>
                                    <?= (!isset($paper["year"]) || $paper["year"] == 0) ? "N/A" : $paper["year"] ?>
                                </span>
                            </div>
                            */ ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
