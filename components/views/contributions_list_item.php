<?php

use app\components\CustomBootstrapModal;
use app\components\ResultItem;
use app\models\AdminOptions;
use app\models\Involvement;
use app\models\SummaryUsage;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\LinkPager;

// Register compact views CSS
$this->registerCssFile('@web/css/compact-views.css', ['depends' => [\yii\bootstrap\BootstrapAsset::className()]]);

$headingType = ! empty($element_config['heading_type']) ? $element_config['heading_type'] : Yii::$app->params['defaultElementHeadingType'];
$canShowSummaryButton = ! empty($element_config['enable_summary']) &&
    (int) $element_config['enable_summary'] === 1 &&
    ($works_num ?? 0) > 0 &&
    ! empty($papers) &&
    SummaryUsage::isAiAssistantEnabledForCurrentUser();

if ($canShowSummaryButton) {
    $threshold = AdminOptions::getValue('summarize_button_threshold') ?? 20;
    // Use all_papers if available (for pagination support), otherwise fall back to current page papers
    $papersForSummary = ! empty($result['all_papers']) ? $result['all_papers'] : $papers;
    $paperIdsForSummary = json_encode(array_map(function ($paper) {
        return $paper['internal_id'];
    }, $papersForSummary));
}

?>

<div class="row" style="display: flex; align-items: center;">
    <div class="<?= $canShowSummaryButton ? 'col-md-8 col-xs-12' : 'col-md-12 col-xs-12' ?>">
        <?php if (! empty($element_config['show_header'])): ?>
        <<?= $headingType ?> style="display: inline-block;">
            List of works
        </<?= $headingType ?>>
        <?php endif;?>
    </div>
    <?php if (! empty($canShowSummaryButton)): ?>
        <div class="col-md-4 col-xs-12 text-right" style="margin-top: 5px;">
            <button
                class="btn btn-default btn-sm summarizeBtn"
                data-list-id="<?= $list_id ?>"
                data-paper-ids='<?= $paperIdsForSummary ?>'
                data-keywords=""
                data-threshold="<?= $threshold ?>"
                data-summary-mode="modal"
                data-profile-user-id="<?= (int) ($profile_owner_user_id ?? 0) ?>"
                data-profile-name="<?= Html::encode((string) ($profile_owner_name ?? '')) ?>"
            >
                <i class="fa-solid fa-wand-magic-sparkles"></i> Summarize top contributions
            </button>
        </div>
    <?php endif; ?>
</div>

<div class='row'>
    <div id="loading_results" class="col-md-offset-4 col-md-4 text-center">
        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <br/><br/>
        Loading publications (it may take a couple of seconds)...
    </div>
</div>

<div id="publications">
    <?php
    $showPager = ! empty($element_config['show_pagination']) &&
            ($works_num ?? 0) > 0 &&
            ! empty($result['pagination']);

    $hideMeta = ! empty($element_config['user_defined']) &&
        (int) $element_config['user_defined'] === 1 &&
        (int) ($result['selected_papers_num'] ?? 0) === 0;

    $rightShown = ! $hideMeta && empty($element_config['top_k']) && ($works_num ?? 0) > 0;

    ?>
    <div class='row' style="align-items:center;">
        <?php if ($showPager): ?>
            <div class="col-md-4 text-left results-header"
                style="display:flex;align-items:center;flex-wrap:nowrap;">
                <?php if (! empty($preHeaderHtml)): ?>
                    <?= $preHeaderHtml ?>&nbsp;&nbsp;&nbsp;
                <?php endif; ?>
                <?php if (! $hideMeta && ($works_num ?? 0) > 0): ?>
                    <?php
                    $hasPager = ! empty($result['pagination']);
                    $totalResults = $hasPager
                        ? (int) $result['pagination']->totalCount
                        : (isset($result['papers_num']) ? (int) $result['papers_num'] : count($result['papers'] ?? []));
                    $pageCount = $hasPager ? (int) $result['pagination']->pageCount : 1;
                    $topK = isset($element_config['top_k']) ? (int) $element_config['top_k'] : 0;
                    $isUserDefined = ! empty($element_config['user_defined']) && (int) $element_config['user_defined'] === 1;
                    $maxSelection = (isset($element_config['user_defined_max']) && $element_config['user_defined_max'] !== '')
                        ? (int) $element_config['user_defined_max']
                        : null;
                    ?>
                    <?php if (! empty($element_config['top_k'])): ?>
                        <span style="white-space:nowrap;">
                            Top <?= Yii::$app->formatter->asDecimal(min($topK, $totalResults), 0) ?> results
                            &nbsp;sorted by&nbsp;<?= Html::tag('i', $orderings[$sort_field] ?? ucfirst($sort_field)) ?>
                        </span>
                    <?php else: ?>
                        <span style="white-space:nowrap;">
                            <?= Yii::$app->formatter->asDecimal($totalResults, 0) ?> results<?= ($isUserDefined && $maxSelection !== null) ? ' out of ' . Yii::$app->formatter->asDecimal($maxSelection, 0) . ' available' : '' ?>
                            <?php if ($hasPager && $pageCount > 1): ?>
                            (<?= Yii::$app->formatter->asDecimal($pageCount, 0) ?> pages)
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class='col-md-4 text-center'>
            <?= LinkPager::widget([
                'pagination' => $result['pagination'],
                'maxButtonCount' => 5,
                'options' => ['class' => 'pagination bip-link-pager']
            ]); ?>
            </div>

            <?php if ($rightShown): ?>
                <div class="col-md-4 text-right" style="margin-top:5px">
                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                    <?= Html::dropDownList('sort_' . $list_id, $sort_field, $orderings, [
                    'id' => 'sort-dropdown-' . $list_id,
                    'class' => 'sort-dropdown',
                    'form' => $formId,
                    'onchange' => 'submit_scholar_form();'
                    ]) ?>
                </div>
            <?php else: ?>
                <div class="col-md-4"></div>
            <?php endif; ?>

        <?php else: ?>
            <div class="col-md-8 text-left results-header"
                style="display:flex;align-items:center;flex-wrap:nowrap;">
                <?php if (! empty($preHeaderHtml)): ?>
                    <?= $preHeaderHtml ?>&nbsp;&nbsp;&nbsp;
                <?php endif; ?>

                <?php if (! $hideMeta && ($works_num ?? 0) > 0): ?>
                    <?php
                    $hasPager = ! empty($result['pagination']);
                    $totalResults = $hasPager
                        ? (int) $result['pagination']->totalCount
                        : (isset($result['papers_num']) ? (int) $result['papers_num'] : count($result['papers'] ?? []));
                    $isUserDefined = ! empty($element_config['user_defined']) && (int) $element_config['user_defined'] === 1;
                    $maxSelection = (isset($element_config['user_defined_max']) && $element_config['user_defined_max'] !== '')
                        ? (int) $element_config['user_defined_max']
                        : null;
                    ?>
                    <span style="white-space:nowrap;">
                        <?= Yii::$app->formatter->asDecimal($totalResults, 0) ?> results<?= ($isUserDefined && $maxSelection !== null) ? ' out of ' . Yii::$app->formatter->asDecimal($maxSelection, 0) . ' available' : '' ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($rightShown): ?>
                <div class="col-md-4 text-right" style="margin-top:5px">
                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                    <?= Html::dropDownList('sort_' . $list_id, $sort_field, $orderings, [
                    'id' => 'sort-dropdown-' . $list_id,
                    'class' => 'sort-dropdown',
                    'form' => $formId,
                    'onchange' => 'submit_scholar_form();'
                    ]) ?>
                </div>
            <?php endif; ?>
            <?php endif; ?> 
    </div>

    <?php if (! empty($canShowSummaryButton)): ?>
        <?php Modal::begin([
            'id' => 'summary-modal-' . $list_id,
            'header' => '<h4 class="modal-title"><i class="fa-solid fa-wand-magic-sparkles"></i> Summary of top contributions</h4>',
            'size' => 'modal-lg',
        ]); ?>
            <div
                id="summary_panel_<?= $list_id ?>"
                class="summary_panel"
                data-list-id="<?= $list_id ?>"
            >
                <div class="grey-text">
                    <div class="summary-controls">
                        <div
                            class="regenerate-summary-box"
                            id="regenerate-summary-box-<?= $list_id ?>"
                            style="display: none;"
                            data-list-id="<?= $list_id ?>"
                        >
                            <label for="summary-count-<?= $list_id ?>" class="regenerate-label">Use top</label>
                            <input
                                type="number"
                                id="summary-count-<?= $list_id ?>"
                                class="regenerate-input summary-count-input"
                                data-list-id="<?= $list_id ?>"
                            />
                            <label for="summary-count-<?= $list_id ?>" class="regenerate-label">results.</label>
                            <span
                                role="button"
                                data-toggle="popover"
                                data-placement="auto"
                                title="AI Summary"
                                data-content="<p>The summary format will change based on the selected number of papers:</p>
                                <ul>
                                    <li>1-5 papers: Produces a concise overview.</li>
                                    <li>6-20 papers: Creates a more detailed, literature review-style summary.</li>
                                </ul>"
                                style="cursor: pointer;"
                            >
                                <small><i class="fa fa-info-circle light-grey-link" aria-hidden="true"></i></small>
                            </span>

                            <button
                                class="btn btn-sm btn-custom-color regenerate-button regenerate-summary-btn"
                                data-list-id="<?= $list_id ?>"
                            >
                                Summarize
                            </button>
                        </div>
                        <div
                            class="text-right copy-summary-wrapper"
                            id="copy-summary-wrapper-<?= $list_id ?>"
                            style="display: none;"
                            data-list-id="<?= $list_id ?>"
                        >
                            <a
                                class="btn btn-default btn-xs fs-inherit grey-link copy-summary-btn"
                                role="button"
                                data-toggle="tooltip"
                                data-list-id="<?= $list_id ?>"
                            >
                                <i class="fa fa-copy" aria-hidden="true"></i> Copy to clipboard
                            </a>
                        </div>
                    </div>

                    <div
                        class="text-center summary-loading-centered summaryLoading"
                        id="summaryLoading-<?= $list_id ?>"
                        data-list-id="<?= $list_id ?>"
                    >
                        <i class="fa fa-spinner fa-spin"></i> Generating summary...
                    </div>

                    <div
                        class="summaryText"
                        id="summaryText-<?= $list_id ?>"
                        style="text-align: justify; display: none;"
                        data-list-id="<?= $list_id ?>"
                    ></div>
                    <div
                        class="summary-usage-info summaryUsageInfo"
                        id="summary-usage-info-<?= $list_id ?>"
                        data-list-id="<?= $list_id ?>"
                    ></div>
                </div>
            </div>
        <?php Modal::end(); ?>
    <?php endif; ?>

        <?php if ($works_num > 0): ?>
            <div id='results_tbl' class='row'>
                <div class="col-xs-12">
                    <?php
                    try {
                        foreach ($papers as $paper) {
                            echo ResultItem::widget([
                                'impact_indicators' => $impact_indicators,
                                'internal_id' => $paper['internal_id'],
                                'contribution_list_id' => $list_id,
                                'edit_perm' => $edit_perm,
                                'doi' => $paper['doi'],
                                'dois_num' => $paper['dois_num'],
                                'openaire_id' => $paper['openaire_id'],
                                'title' => $paper['title'],
                                'authors' => $paper['authors'],
                                'journal' => $paper['journal'],
                                'year' => $paper['year'],
                                'concepts' => $paper['concepts'],
                                'relations' => $paper['relations'],
                                'has_dataset' => $paper['has_dataset'] ?? false,
                                'has_software' => $paper['has_software'] ?? false,
                                'tags' => $paper['tags'],
                                'involvements' => Involvement::getInvolvementFieldsByWorkType($paper['type']),
                                'involved' => $paper['involvement'],
                                'pop_score' => $paper['attrank'],
                                'inf_score' => $paper['pagerank'],
                                'imp_score' => $paper['3y_cc'],
                                'cc_score' => $paper['citation_count'],
                                'pop_class' => $paper['pop_class'],
                                'inf_class' => $paper['inf_class'],
                                'imp_class' => $paper['imp_class'],
                                'cc_class' => $paper['cc_class'],
                                'is_oa' => $paper['is_oa'],
                                'type' => $paper['type'],
                                'software_metadata' => $paper['software_metadata'] ?? null,
                                'view_mode' => $element_config['compact_view'] ?? 'full',
                                'show' => [
                                    'concepts' => true,
                                    'relations' => true,
                                    'tags' => false,
                                    'involvement' => true,
                                ],
                                'profile_owner_user_id' => $profile_owner_user_id ?? null,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        var_dump('Error inside ResultItem', $e->getMessage());
                        exit;
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
</div>
<?= CustomBootstrapModal::widget(['id' => 'versions-modal']) ?>
<?= CustomBootstrapModal::widget(['id' => 'relations-modal']) ?>
    
<?php if (! empty($noWorksMessage)): ?>
    <?= $noWorksMessage ?>
<?php elseif ($works_num === 0): ?>
    <div class="alert alert-warning text-center" role="alert">
        No research products of this type were found in the public works of the respective ORCID profile.
    </div>
<?php endif; ?>


<?php if ($missing_papers_num > 0 && ! isset($current_cv_narrative) && $show_missing_works): ?>
    <div id="missing-publications-toggle-<?= $list_id ?>" class="col-md-12 text-center">
        <button type="button" class="btn btn-link missing-publications-toggle main-green"
        data-toggle="collapse" data-target="#missing-publications-<?= $list_id ?>">
            <b>Missing works (<?= $missing_papers_num ?>)</b> </i>
        </button>
    </div>
    <div id="missing-publications-<?= $list_id ?>" class="collapse">
        <div class="row" >
            <div class="col-md-8">
                <h3>
                    <span role="button" data-toggle="popover" data-placement="auto" title="Missing works" data-content="<div><span class='green-bip'></span><?= 'This list contains works retrieved from ORCiD that BIP! software do not contain in its database' ?></div>"> Missing works  <small><i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></small></span>
                </h3>
            </div>
        </div>
        <table class="table table-hover">
            <tbody>
                <?php foreach ($missing_papers as $paper): ?>
                    <tr class="text-left ">
                        <td class="col-xs-8">

                            <!-- title -->
                            <div <?php if (isset($paper['title']) && strlen($paper['title']) > 90) { ?> title="<?= $paper['title'] ?>" <?php } ?>>
                                <?= (! isset($paper['title'])) ? 'N/A' : Yii::$app->bipstring->shortenString($paper['title'], 180) ?>
                            </div>

                            <div class="year-venue-bookmarks">

                                <!-- venue -->
                                <span <?php if (isset($paper['journal']) && strlen($paper['journal']) > 60) { ?> title="<?= $paper['journal'] ?>" <?php } ?>>
                                    <?= (! isset($paper['journal']) || trim($paper['journal']) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($paper['journal'], 60)?>
                                </span>&middot;

                                <!-- year -->
                                <span>
                                    <?= (! isset($paper['year']) || $paper['year'] == 0) ? 'N/A' : $paper['year'] ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>