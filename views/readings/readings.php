<?php

use app\components\ResultItem;
use app\components\SummaryFormatter;
use app\models\Involvement;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = 'BIP! Services - My readings';

$this->registerJsFile('@web/js/third-party/bootstrap-tagsinput/bootstrap-tagsinput.min.js', ['position' => View::POS_END]);
$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/reading-status.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/favoriteTags.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/tinymceModal.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholar-readings.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/readings.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholarInvolvement.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/fixed-sidebar.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJs('window.bipScholarFacetConfig = ' . json_encode(['softwareRoleIds' => array_map('strval', array_keys(\Yii::$app->params['involvement_fields']['software'] ?? []))]) . ';', View::POS_END);

$this->registerCssFile('@web/css/tags.css');
$this->registerCssFile('@web/css/reading-status.css');
$this->registerCssFile('@web/css/readings.css');
$this->registerCssFile('@web/css/scholar-profile.css');
$this->registerCssFile('@web/css/on-off-switch.css');
$this->registerCssFile('@web/css/fixed-sidebar.css');

$papers_num = $result['papers_num'];
$papers = $result['papers'];
$summaryThreshold = \app\models\AdminOptions::getValue('summarize_button_threshold') ?? 20;
$paperIdsForSummary = json_encode(array_map(function ($p) { return $p['internal_id']; }, $papers));
$facetPreviewLimit = 10;
$renderFacetToggle = static function (int $itemsCount): string {
    if ($itemsCount <= 10) {
        return '';
    }

    return '<button type="button" class="btn btn-xs js-facet-see-more facet-see-more-btn grey-link fs-inherit" aria-expanded="false">Show more</button>';
};

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>

<!-- Latest compiled and minified CSS -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css"> -->

<!-- Latest compiled and minified JavaScript -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script> -->

<!-- Comparison bar -->
<span class="jumbotron">
    <a href='<?=Url::to(['site/comparison'])?>' target='_blank' id='comparison' class='btn btn-warning'></a>
    <div id='clear-comparison'  onclick="clearSelected();">
        Clear all
        <i class="fa fa-times" aria-hidden="true"></i>
    </div>
</span>

<div id="readings" class="container-fluid">
    <div class="<?= $edit_perm ? 'main-content' : '' ?>">
    <div class="row">
        <div class="col-xs-12 col-sm-9">
            <h1>
                <?php if (isset($current_reading_list)): ?>
                    <?= Html::encode($current_reading_list->title) ?>
                    <?php if ($edit_perm) : ?>
                        <span role="button"
                              data-toggle="modal"
                              data-target="#new-reading-list-modal"
                              data-mode="edit"
                              data-reading-list-id="<?= $current_reading_list->id ?>"
                              data-reading-list-title="<?= Html::encode($current_reading_list->title) ?>"
                              data-reading-list-description="<?= Html::encode($current_reading_list->description ?? '') ?>"
                              class="grey-link small"
                              title="Edit current list">
                            <i class="fa-solid fa-pen-to-square fa-2xs"></i>
                        </span>
                        <a href="<?= Url::to(['readings/delete-reading-list/', 'selected_list_id' => $current_reading_list->id]) ?>" class="grey-link small" title="Delete current list" onclick="return confirm('Are you sure you want to delete this reading list?');"><i class="fa-solid fa-trash fa-2xs"></i></a>
                    <?php endif; ?>
                    <small class="grey-text reading-powered-by">Powered-by <a href="<?= Url::to(['readings/index']) ?>" class="green-bip"><?= Html::img('@web/img/bip-minimal.png', ['alt' => 'BIP! Readings', 'style' => 'height:14px; width:auto;']) ?></a></small>
                <?php else: ?>
                    My readings
                <?php endif; ?>
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 text-right">
            <?php if (isset($current_reading_list)): ?>
                <input id='current_reading_list_id' name='current_reading_list_id' value='<?= $current_reading_list->id ?>' type='hidden'/>
                <?php if ($edit_perm): ?>
                    <div id="reading-list-public-btn" style = "display:inline-block;">
                        <div class="onoffswitch2">
                            <input type="checkbox" class="onoffswitch2-checkbox" id="reading-list-public-switch" <?= ($current_reading_list->is_public) ? 'checked' : '' ?>>
                            <label class="onoffswitch2-label" for="reading-list-public-switch">
                                <span class="onoffswitch2-inner"></span>
                                <span class="onoffswitch2-switch"></span>
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php if (isset($current_reading_list)): ?>
            <div class="col-xs-12">
                <?php
                    $fullDescription = (empty($current_reading_list->description))
                        ? 'No description is provided for this reading list.'
                        : $current_reading_list->description;

                    [$descriptionBody, $referenceMap] = SummaryFormatter::prepareDescriptionForDisplay($fullDescription);

                    $maxDescriptionChars = 440;
                    $isDescriptionTruncated = mb_strlen($descriptionBody) > $maxDescriptionChars;
                    $shortDescription = $isDescriptionTruncated
                        ? mb_substr($descriptionBody, 0, $maxDescriptionChars) . '...'
                        : $descriptionBody;

                    $fullDescriptionHtml = SummaryFormatter::renderCitationHtml($descriptionBody, $referenceMap);
                    $shortDescriptionHtml = SummaryFormatter::renderCitationHtml($shortDescription, $referenceMap);
                ?>
                <p class="grey-text reading-list-description reading-list-description-inline">
                    <span id="reading-list-description"
                          data-short-html="<?= Html::encode($shortDescriptionHtml) ?>"
                          data-full-html="<?= Html::encode($fullDescriptionHtml) ?>"
                          data-expanded="0"><?= $shortDescriptionHtml ?></span>
                    <?php if ($isDescriptionTruncated): ?>
                        <button id="reading-list-description-toggle" type="button" class="btn btn-link btn-xs grey-link">See more</button>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
        <div class="well profile">

            <div class="<?= $edit_perm ? 'col-sm-9' : 'col-sm-12' ?> col-xs-12">

                <?php ActiveForm::begin([
                    'id' => 'scholar-form',
                    'method' => 'get',
                    'action' => isset($current_reading_list)
                        ? Url::to(['readings/list/' . $current_reading_list->id])
                        : Url::to(['readings/list']),
                    'options' => ['data-selected_list_id' => isset($current_reading_list) ? $current_reading_list->id : '']
                ]); ?>

                <div class="facet-row">
                    <div class="facet-header grey-text">
                        <i class="fa-solid fa-atom" aria-hidden="true" title="Topics"></i> <strong>
                            <span role="button" data-toggle="popover" data-placement="auto" title="<b>Topics</b>" data-content="<div><span class='green-bip'></span> Topics are abstract concepts that works are about. In particular, we use the (L2) topics from OpenAlex. <a target='_blank' class='green-bip' href='https://docs.openalex.org/api-entities/concepts'><br/>see more <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> Topics <i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></span>
                        </strong>
                        <?= (! empty($selected_topics)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'topics[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                    </div>
                    <input id='fct_field' name='fct_field' value='' type='hidden'/>

                    <?php if (count($result['facets']['topics']['counts']) == 0) { ?>
                            <span id="topic-facet-items">-</span>
                    <?php } else {
                    $counts = $result['facets']['topics']['counts'];

                    echo Html::checkboxList('topics', $selected_topics, $result['facets']['topics']['options'], [
                                'id' => 'topic-facet-items',
                                'style' => ['display' => 'inline'],
                                'item' => function ($index, $label, $name, $checked, $value) use ($counts, $edit_perm, $facetPreviewLimit) {
                                    $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                    $disabled = ($checked) ? '' : 'disabled=disabled';
                                    $btn_disabled = '';
                                    $hidden_class = ($index >= $facetPreviewLimit && ! $checked) ? ' facet-item-hidden' : '';

                                    return "<button id='topic-${value}' type='button' class='btn btn-xs ${btn_class} facet-item${hidden_class}' ${btn_disabled}>
                                        <input id='topic-${value}-i' name='topics[]' value='${value}' type='hidden' ${disabled}/>
                                        ${label} <span class='badge badge-primary'>{$counts[$value]}</span>
                                    </button>";
                                }
                            ]);
                    echo $renderFacetToggle(count($result['facets']['topics']['options']));
                }
                    ?>
                </div>

                <?php if (! isset($current_reading_list)): ?>
                    <div class="facet-row">
                        <div class="facet-header grey-text">
                            <i class="fa fa-tags" aria-hidden="true" title="User-defined tags"></i> <strong>
                                <span role="button" data-toggle="popover" data-placement="auto" title="<b>User-defined tags</b>" data-content="<div><span class='green-bip'></span> User-defined tags are labels assigned to the readings by the end-user that can help searching for particular works and/or organizing them into reading lists.</div>"> User-defined tags <i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></span>
                            </strong>
                            <?= (! empty($selected_tags)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'tags[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                        </div>

                        <?php if (count($result['facets']['tags']['counts']) == 0) { ?>
                                <span id="tag-facet-items">-</span>
                        <?php } else {
                        $counts = $result['facets']['tags']['counts'];

                        echo Html::checkboxList('tags', $selected_tags, $result['facets']['tags']['options'], [
                                    'id' => 'tag-facet-items',
                                    'style' => ['display' => 'inline'],
                                    'item' => function ($index, $label, $name, $checked, $value) use ($counts, $edit_perm, $facetPreviewLimit) {
                                        $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                        $disabled = ($checked) ? '' : 'disabled=disabled';
                                        $btn_disabled = '';
                                        $hidden_class = ($index >= $facetPreviewLimit && ! $checked) ? ' facet-item-hidden' : '';

                                        return "<button id='tag-${value}' type='button' class='btn btn-xs ${btn_class} facet-item${hidden_class}' ${btn_disabled}>
                                            <input id='tag-${value}-i' name='tags[]' value='${value}' type='hidden' ${disabled}/>
                                            ${label} <span class='badge badge-primary'>{$counts[$value]}</span>
                                        </button>";
                                    }
                                ]);
                        echo $renderFacetToggle(count($result['facets']['tags']['options']));
                    }
                        ?>
                    </div>
                <?php endif; ?>
                <?php if ($edit_perm): ?>
                    <div class="facet-row">
                        <div class="facet-header grey-text">
                            <i class="fas fa-glasses"></i> <strong>Reading status</strong><?= (! empty($selected_rd_status)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'rd_status[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                        </div>
                        <?php if (count($result['facets']['rd_status']['counts']) == 0) { ?>
                            <span id="rd_status-facet-items">-</span>
                        <?php } else {
                            $counts = $result['facets']['rd_status']['counts'];

                            echo Html::checkboxList('roles', $selected_rd_status, $result['facets']['rd_status']['options'], [
                                    'id' => 'rd_status-facet-items',
                                    'style' => ['display' => 'inline'],
                                    'item' => function ($index, $label, $name, $checked, $value) use ($counts, $edit_perm, $facetPreviewLimit) {
                                        $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                        $disabled = ($checked) ? '' : 'disabled=disabled';
                                        $btn_disabled = '';
                                        $hidden_class = ($index >= $facetPreviewLimit && ! $checked) ? ' facet-item-hidden' : '';

                                        return "<button id='rd_status-${value}' type='button' class='btn btn-xs ${btn_class} facet-item${hidden_class}' ${btn_disabled}>
                                            <input id='rd_status-${value}-i' name='rd_status[]' value='${value}' type='hidden' ${disabled}/>
                                            ${label} <span class='badge badge-primary'>{$counts[$value]}</span>
                                        </button>";
                                    }
                                ]);
                            echo $renderFacetToggle(count($result['facets']['rd_status']['options']));
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <div class="facet-row">
                        <div class="facet-header grey-text">
                            <i class="fas fa-lock-open" aria-hidden="true" title="Open access data"></i> <strong>Availability</strong><?= (! empty($selected_accesses)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'accesses[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                        </div>
                        <?php if (count($result['facets']['accesses']['counts']) == 0) { ?>
                            <span id="access-facet-items">-</span>
                        <?php } else {
                            $counts = $result['facets']['accesses']['counts'];

                            echo Html::checkboxList('accesses', $selected_accesses, $result['facets']['accesses']['options'], [
                                    'id' => 'access-facet-items',
                                    'style' => ['display' => 'inline'],
                                    'item' => function ($index, $label, $name, $checked, $value) use ($counts, $edit_perm, $facetPreviewLimit) {
                                        $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                        $disabled = ($checked) ? '' : 'disabled=disabled';
                                        $btn_disabled = '';
                                        $label = $label['name'];
                                        $hidden_class = ($index >= $facetPreviewLimit && ! $checked) ? ' facet-item-hidden' : '';

                                        return "<button id='access-${value}' type='button' class='btn btn-xs ${btn_class} facet-item${hidden_class}' ${btn_disabled}>
                                            <input id='access-${value}-i' name='accesses[]' value='${value}' type='hidden' ${disabled}/>
                                            ${label} <span class='badge badge-primary'>{$counts[$value]}</span>
                                        </button>";
                                    }
                                ]);
                            echo $renderFacetToggle(count($result['facets']['accesses']['options']));
                        }
                        ?>
                </div>
                <div class="facet-row">
                            <div class="facet-header grey-text">
                                <i class="fas fa-cube" aria-hidden="true" title="Work types"></i> <strong>Work type</strong><?= (! empty($selected_types)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'types[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                            </div>
                            <?php if (count($result['facets']['types']['counts']) == 0) { ?>
                                <span id="types-facet-items">-</span>
                            <?php } else {
                            $counts = $result['facets']['types']['counts'];

                            echo Html::checkboxList('types', $selected_types, $result['facets']['types']['options'], [
                                        'id' => 'type-facet-items',
                                        'style' => ['display' => 'inline'],
                                        'item' => function ($index, $label, $name, $checked, $value) use ($counts, $edit_perm, $facetPreviewLimit) {
                                            $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                            $disabled = ($checked) ? '' : 'disabled=disabled';
                                            $btn_disabled = '';
                                            $label = $label['name'];
                                            $hidden_class = ($index >= $facetPreviewLimit && ! $checked) ? ' facet-item-hidden' : '';

                                            return "<button id='type-${value}' type='button' class='btn btn-xs ${btn_class} facet-item${hidden_class}' ${btn_disabled}>
                                                <input id='type-${value}-i' name='types[]' value='${value}' type='hidden' ${disabled}/>
                                                ${label} <span class='badge badge-primary'>{$counts[$value]}</span>
                                            </button>";
                                        }
                                    ]);
                            echo $renderFacetToggle(count($result['facets']['types']['options']));
                        }
                            ?>
                        </div>
            </div>
            <?php if ($edit_perm): ?>
            <div class="col-sm-3 col-xs-12 sidebar">
                <div id="toc-panel">
                    <div class="sidebar-body">
                        <h5 class="toc-heading" style="margin-top: 0; margin-bottom: 8px;">
                            <a class="green-bip" href="<?= Url::to(['readings/list']) ?>"><strong>My readings</strong></a>
                        </h5>
                        <h5 class="toc-heading">
                            <span class="green-bip">
                                Reading lists
                                <span role="button" data-toggle="popover" data-placement="auto" title="<b>Reading lists</b>" data-content="<div><span class='green-bip'></span> Create lists from the current filters and access them here.</div>">
                                    <i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i>
                                </span>
                            </span>
                            <span class="pull-right">
                        <?php if ($reading_list_enable): ?>
                            <span role="button" class="grey-link" data-toggle="modal" data-target="#new-reading-list-modal" data-mode="create" title="Save current filters as a new reading list.">
                                <i class="fa-solid fa-plus" aria-hidden="true"></i> Save as new
                            </span>
                        <?php else :
                            $reading_list_title = 'Reading lists are created using user-defined tags only. Please clear any selected Topics, Availability, Reading status, or Work type filters.';
                        ?>
                            <span class="grey-link disabled" title="<?= $reading_list_title ?>">
                                <i class="fa-solid fa-plus" aria-hidden="true"></i> Save as new
                            </span>
                        <?php endif; ?>
                            </span>
                        </h5>

                    <?php if (empty($reading_lists)): ?>
                        <ul>
                            <li class="toc-item empty-reading-lists">
                                <em>No reading lists yet.</em>
                            </li>
                        </ul>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($reading_lists as $reading_list): ?>
                                <?php
                                    $list_id = $reading_list->id;
                                    $list_title = $reading_list->title;
                                    $list_description = trim((string) $reading_list->description);
                                    $list_description_title = $list_description !== ''
                                        ? $list_description
                                        : 'No description is provided for this reading list.';
                                ?>
                                <li class="toc-item">
                                    <a class="toc-link reading-list-item-inline <?= (isset($current_reading_list) && $list_id == $current_reading_list->id) ? 'green-bip' : '' ?>" href="<?= Url::to(['readings/list/' . $list_id]) ?>">
                                        <span class="reading-list-item-title"><?= Html::encode($list_title) ?></span>
                                        <span class="light-grey-link" title="<?= Html::encode($list_description_title) ?>">
                                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    </div>

    <div class="<?= $edit_perm ? 'main-content' : '' ?>">
    <div class="row" >
        <?php if ($papers_num > 0): ?>
        <div class="col-md-8">
            <h4 style="display: inline-block;">
                <b>My readings</b>
            </h4>
        </div>
        <div class="col-md-4 text-right">
            <i class="fa-solid fa-arrow-down-wide-short"></i>
            <?= Html::dropDownList('sort', $sort_field, $orderings, ['id' => 'sort-dropdown']) ?>
        </div>
        <?php endif; ?>
    </div>
    <?php ActiveForm::end(); ?>

    <div class='row'>
        <div id="loading_results" class="col-md-offset-4 col-md-4 text-center">
            <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <br/><br/>
            Loading publications (it may take a couple of seconds)...
        </div>
    </div>
    <?php if ($papers_num > 0): ?>
        <div id="publications">
            <div class='row'>
                <div class='col-md-4 text-left results-header'>
                    <?= Yii::$app->formatter->asDecimal($result['pagination']->totalCount, 0) ?> results
                    <?php if ($result['pagination']->pageCount > 1): ?>
                        (<?=  Yii::$app->formatter->asDecimal($result['pagination']->pageCount, 0) ?> pages)
                    <?php endif; ?>
                </div>
                <div class='col-md-4 text-center'><?= LinkPager::widget([
                    'pagination' => $result['pagination'],
                    'maxButtonCount' => 5,
                    'options' => ['class' => 'pagination bip-link-pager']
                ]); ?></div>
            </div>
            <div id='results_tbl' class='row'>
                <?php foreach ($papers as $paper) {
                    echo ResultItem::widget([
                        'impact_indicators' => $impact_indicators,
                        'internal_id' => $paper['internal_id'],
                        'edit_perm' => $edit_perm,
                        'doi' => $paper['doi'],
                        'title' => $paper['title'],
                        'authors' => $paper['authors'],
                        'journal' => $paper['journal'],
                        'year' => $paper['year'],
                        'user_id' => true,
                        'concepts' => $paper['concepts'],
                        'reading_status' => $paper['reading_status'],
                        'reading_status_choices' => Yii::$app->params['reading_fields'],
                        'tags' => $paper['tags'],
                        'notes' => $paper['notes'],
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
                        'show' => [
                            'concepts' => true,
                            'tags' => ! isset($current_reading_list),
                            'reading_status' => true,
                            'notes' => true,
                            'bookmark' => true,
                        ]
                    ]);
                } ?>
            </div>
        <?php else: ?>
            <span>You do not have any readings to BIP! Scholar. Please add publications to your readings first.</span>
        <?php endif; ?>
    </div>
    </div>
</div>

<?php
    Modal::begin(['options' => ['class' => 'modal fade', 'id' => 'text-editor-modal'],
                    'size' => 'modal-lg',
                    'closeButton' => false,
                    'clientOptions' => ['backdrop' => 'static', 'keyboard' => false]
                ]);

    echo '
        <span id="loading-notes-message" style = "display:none;">
            <center><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br/><br/>
            Loading (it may take a couple of seconds)...</center>
        </span> ';
    Modal::end();
?>

<div id="new-reading-list-modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 id="reading-list-modal-title" class="modal-title">
            Create new reading list
        </h4>
      </div>
      <form id="save-reading-list-form" autocomplete="off" method="POST" action="<?= Url::to(['readings/save-reading-list'])?>">
        <div class="modal-body">
            <input id="reading_list_id" name="reading_list_id" type="hidden" value="" />
            <div class="form-group bip-focus">
                <label for="new_reading_list_title">Name:</label>
                <input id="new_reading_list_title" name="new_reading_list_title" class = "form-control" required="true"/>
            </div>
            <div class="form-group bip-focus">
                <label for="new_reading_list_description" style="display:flex;align-items:center;justify-content:space-between;">
                    <span>Description:</span>
                    <?php if (\app\models\SummaryUsage::isAiAssistantEnabledForCurrentUser()): ?>
                        <button id="reading-list-summarize-btn"
                                type="button"
                                class="btn btn-default btn-xs reading-list-summarize-btn"
                                title="Use AI to summarize top results and fill the description automatically.">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Autogenerate with AI
                        </button>
                    <?php endif; ?>
                </label>
                <textarea id="new_reading_list_description" name="new_reading_list_description" class = "form-control" style = "resize: vertical; min-height: 180px;"></textarea>
            </div>
            <input id='new_reading_list_facets' name='new_reading_list_facets' type='hidden' value='<?= json_encode([
                'tags' => $selected_tags,
                'accesses' => $selected_accesses,
                'rd_status' => $selected_rd_status,
                'types' => $selected_types,
                'sort' => $sort_field,
            ]) ?>' />
            <input id="reading-list-summarize-paper-ids" type="hidden" value='<?= Html::encode($paperIdsForSummary) ?>' />
            <input id="reading-list-summarize-threshold" type="hidden" value="<?= (int) $summaryThreshold ?>" />
        </div>
        <div class="modal-footer">
            <button id="reading-list-modal-submit" class="btn btn-success" type="submit" name="submit" value="Submit">Save</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

