<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\LinkPager;
use app\components\ResultItem;
use app\components\CustomBootstrapModal;

$headingType = !empty($element_config['heading_type']) ? $element_config['heading_type'] : Yii::$app->params['defaultElementHeadingType'];

?>

<div class="row">
    <div class="col-md-12">
        <?php if (!empty($element_config["show_header"])): ?>
        <<?= $headingType ?> style="display: inline-block;">
            List of works
        </<?= $headingType ?>>
        <?php endif;?>
    </div>
</div>

<div class='row'>
    <div id="loading_results" class="col-md-offset-4 col-md-4 text-center">
        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <br/><br/>
        Loading publications (it may take a couple of seconds)...
    </div>
</div>

<?php if ($works_num > 0): ?>
    <div id="publications">
        <div class='row'>
            <div class='col-md-4 text-left results-header'>
                <?= !empty($element_config['top_k']) ? "Top" : "" ?>
                    <?= Yii::$app->formatter->asDecimal($result['pagination']->totalCount, 0) ?> results
                <?php if ($result['pagination']->pageCount > 1): ?>
                    (<?=  Yii::$app->formatter->asDecimal($result['pagination']->pageCount,0) ?> pages)
                <?php endif; ?>
                <?= !empty($element_config['top_k']) ? "sorted by " . Html::tag('i', $orderings[$sort_field]) : "" ?>
            </div>
            <div class='col-md-4 text-center'><?= LinkPager::widget([
                'pagination' => $result['pagination'],
                'maxButtonCount' => 5,
                'options' => ['class' => 'pagination bip-link-pager']
            ]); ?></div>

            <?php if (empty($element_config['top_k'])): ?>
                <div class="col-md-4 text-right" style="margin-top:5px">
                    <i class="fa-solid fa-arrow-down-wide-short"></i> <?= Html::dropDownList('sort', $sort_field, $orderings, ['id' => 'sort-dropdown', 'form' => $formId , 'onchange' => 'submit_scholar_form();']) ?>
            </div>
            <?php endif; ?>
        </div>
        <div id='results_tbl' class='row'>
            <div class="col-xs-12">
                <?php foreach ($papers as $paper) {
                    echo ResultItem::widget([
                        "impact_indicators" => $impact_indicators,
                        "internal_id" => $paper["internal_id"],
                        "edit_perm" => $edit_perm,
                        "doi" => $paper["doi"],
                        "dois_num" => $paper["dois_num"],
                        "openaire_id" => $paper["openaire_id"],
                        "title" => $paper["title"],
                        "authors" => $paper["authors"],
                        "journal" => $paper["journal"],
                        "year" => $paper["year"],
                        "concepts" => $paper["concepts"],
                        "relations" => $paper["relations"],
                        "tags" => $paper["tags"],
                        "involvements" => Yii::$app->params['involvement_fields'],
                        "involved" => $paper["involvement"],
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
                            "relations" => true,
                            "tags" => false,
                            "involvement" => true,
                        ]
                    ]);
                } ?>
            </div>
        </div>
    </div>
    <?= CustomBootstrapModal::widget(['id' => 'versions-modal']) ?>
    <?= CustomBootstrapModal::widget(['id' => 'relations-modal']) ?>
<?php else: ?>
    <div>BIP! software was not able to retrieve any publications for your profile. Also note that BIP Scholar retrieves only public works from your ORCiD profile</div>
<?php endif; ?>



<?php if ($missing_papers_num > 0 && $facets_selected == false && !isset($current_cv_narrative)): ?>
    <div id="missing-publications-toggle" class="col-md-12 text-center">
        <button type="button" class="btn btn-link missing-publications-toggle main-green"
        data-toggle="collapse" data-target="#missing-publications">
            <b>Missing works (<?= $missing_papers_num ?>)</b> </i>
        </button>
    </div>
    <div id="missing-publications" class="collapse">
        <div class="row" >
            <div class="col-md-8">
                <h3>
                    <span role="button" data-toggle="popover" data-placement="auto" title="Missing works" data-content="<div><span class='green-bip'></span><?= "This list contains works retrieved from ORCiD that BIP! software do not contain in its database" ?></div>"> Missing works  <small><i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></small></span>
                </h3>
            </div>
        </div>
        <table class="table table-hover">
            <tbody>
                <?php foreach ($missing_papers as $paper): ?>
                    <tr class="text-left ">
                        <td class="col-xs-8">

                            <!-- title -->
                            <div <?php if (isset($paper["title"]) && strlen($paper["title"]) > 90) { ?> title="<?= $paper['title'] ?>" <?php } ?>>
                                <?= (!isset($paper["title"])) ? 'N/A' : Yii::$app->bipstring->shortenString($paper["title"], 90) ?>
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
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>