<?php

use app\components\BulletedList;
use app\components\ContributionsListItem;
use app\components\DropdownElement;
use app\components\FacetsItem;
use app\components\IndicatorsItem;
use app\components\NarrativeElement;
use app\components\ScholarNavbar;
use app\components\SectionDivider;
use app\components\TableElement;
use yii\bootstrap\Button;
use yii\bootstrap\Modal;
use yii\bootstrap\Nav;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

$this->registerJsFile('@web/js/third-party/chartjs/chart_v4.2.0.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/chartjs_radar.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/countUp/countUp_v2.8.0.umd.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/animateIndicators.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/bootstrap-tagsinput/bootstrap-tagsinput.min.js', ['position' => View::POS_END]);
$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/reading-status.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJs('window.bipScholarFacetConfig = ' . json_encode(['softwareRoleIds' => array_map('strval', array_keys(\Yii::$app->params['involvement_fields']['software'] ?? []))]) . ';', View::POS_END);
$this->registerJsFile('@web/js/scholar-readings.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholarInvolvement.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/responsibleAcadAge.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/cvNarrative.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/profile_visibility.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholar-topic-report.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholarPdfExport.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/papersSelection.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
// Use the generic smooth-scroll handler
$this->registerJsFile('@web/js/scrollToAnchor.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
// Build/update TOC for Section Divider headings
$this->registerJsFile('@web/js/profile-toc.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
// Report profile functionality
$this->registerJsFile('@web/js/profile-report.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
// AI summaries for Contributions Lists
$this->registerJsFile('@web/js/summarize.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerCssFile('@web/css/tags.css');
$this->registerCssFile('@web/css/reading-status.css');
$this->registerCssFile('@web/css/scholar-profile.css');
$this->registerCssFile('@web/css/missing-works.css');
$this->registerCssFile('@web/css/profile-toc.css');

$this->title = 'BIP! Services - Scholar';

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>

<!-- Latest compiled and minified CSS -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css"> -->

<!-- Latest compiled and minified JavaScript -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script> -->

<?php
use yii\widgets\Pjax;

?>

<?php if (! isset($researcher->orcid)): ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <h1>
                Welcome to your BIP! Scholar Profile
                </h1>
            </div>
            <?php if (isset($researcher_exists)): ?>
                <div class="col-xs-12">
                    <div class="alert alert-danger">
                        <div><b>Orcid Authorization Issue</b></div>
                        It seems that the ORCID (<?= $researcher_exists->orcid?>) you're trying to authorize with, is already linked to another account in our system. If you believe this is a mistake, please contact our support team. Alternatively, you can try authorizing with a different ORCID.
                    </div>
                </div>
            <?php endif; ?>
            <div class="col-xs-12">
                <div class="well profile">
                    Link BIP! Scholar with your ORCiD account to allow us access your public ORCID records, your name and ORCiD ID.<br/>
                    Please, also ensure that your ORCiD profile has 'Visibility: Everyone' in your Account Settings, since we rely on the public records of you ORCiD profile to create the contents of your BIP! Scholar profile.
                    <div class="text-center" style="padding-top: 10px;">
                        <?= Html::a('<i class="fa fa-link" aria-hidden="true"></i> Link with your ORCiD', 'https://orcid.org/oauth/authorize?client_id=' . Yii::$app->params['orcid_client_id'] . '&response_type=code&scope=/authenticate&redirect_uri=' . Url::to(['scholar/profile'], true), ['class' => 'btn btn-custom-color', 'style' => 'white-space: break-spaces']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif (isset($researcher->orcid)): ?>
    
    <?php $this->title = 'BIP! Scholar - ' . $researcher->name; ?>

    <!-- Second Navbar -->
    <?= ScholarNavbar::widget([
        'template' => $template,
        'templateDropdownData' => $templateDropdownData,
        'researcher' => $researcher,
        'edit_perm' => $edit_perm,
    ]) ?>

    <?php
        // Avoid errors from trying to render the full view.
        // In controller we don't calculate all respective view variables,
        // when the pjax request comes from cv narrative modal.
        // if (!$is_cv_narrative_pjax):
        if (true): ?>

        <div class="container-fluid">
        <?php ActiveForm::begin([
            'id' => 'scholar-form',
            'options' => ['style' => 'display: inline-block;'],
            'method' => 'GET',
            'action' => Url::to(['scholar/profile/' . $researcher->orcid . (isset($template->url_name) ? ('/' . $template->url_name) : '')])
        ]); ?>

        <?= Html::hiddenInput('list_id', '', ['id' => 'active_list_id']) ?>
        <?php
        // Pre-generate a hidden fct_field for each Contributions List so JS can set it on click
        if (! empty($template_elements)) {
            foreach ($template_elements as $te) {
                if (($te['type'] ?? null) === 'Contributions List') {
                    $lid = $te['element_id'];
                    echo Html::hiddenInput("lists[${lid}][fct_field]", '', ['id' => "lists-{$lid}-fct_field"]);
                }
            }
        }
        ?>

        <?php ActiveForm::end(); ?>

            <div class="row">
                <div class="col-xs-12">
                    <h1>
                        <div class="row">
                            <div class="col-xs-12 col-sm-7">
                                <div class="d-flex" style="align-items: center; flex-wrap: wrap;">
                                    
                                    
                                    <span class="mr-5">
                                        <?= $researcher->name ?>
                                    </span>
                                    
                                    <span>
                                        <small>
                                            <a class="grey-link" href="<?= 'https://orcid.org/' . $researcher->orcid ?>" target="_blank">
                                                <i class="fa-brands fa-orcid" title="Show profile on ORCiD"></i>
                                            </a>
                                        </small>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-xs-12 col-sm-5 text-right">
                                 <!-- show spinner when pdf link is clicked -->
                                 <small id="loading-spinner" style="display: none;">
                                    <i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Exporting PDF
                                </small>
                            </div>
                        </div>
                    </h1>
                </div>
                <?php /*
                <div style="margin-bottom: 15px;">
                    <ul class="nav nav-tabs green-nav-tabs">
                                <li class="<?=!isset($current_cv_narrative) ? 'active' : ''?>">
                                <a class="" <?= isset($current_cv_narrative) ? "href=" . Url::to(['scholar/profile/' . $researcher->orcid]) : "" ?>>Overview</a>
                                </li>
                        <?php if ($edit_perm || (!$edit_perm && $public_cv_narratives_count > 0)) :?>
                                <li role="presentation" class="dropdown <?= isset($current_cv_narrative) ? 'active' : '' ?>">
                                    <a class="dropdown-toggle" style="cursor:pointer" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    Narratives <span class="label label-default <?= isset($current_cv_narrative) ? "label-success" : "" ?>"><?= (!$edit_perm) ? $public_cv_narratives_count : (!empty($cv_narratives) ? count($cv_narratives) : '' ) ?></span> <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-fit-screen green-dropdown">

                            <?php if ($edit_perm) :?>

                                    <li>
                                        <a role="button" id = "cv-narrative-create-button" title = "New CV narrative">
                                        New Narrative
                                        <span onclick="event.stopPropagation();return false;" role="button" data-toggle="popover" data-placement="auto" title="<b>Narrative</b>" data-content="Narratives serve as a tailor-made snapshot of your research works, and provide a concise overview of your professional background, skills and achievements. </div>"> <i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></span>
                                        </a>
                                    </li>
                            <?php endif; ?>

                            <?php
                                if ($edit_perm && !empty($cv_narratives)){
                                    echo '<li role="separator" class="divider"></li>';
                                }

                                foreach($cv_narratives as $cv_narrative) {
                                    if ( $edit_perm || (!$edit_perm && $cv_narrative->is_public) ) {
                                    echo '<li class=" ' . ((isset($current_cv_narrative) && $cv_narrative->id == $current_cv_narrative->id) ? 'active' : '') . '"><a class="" href="' . Url::to(['scholar/profile/' . $researcher->orcid . '/' . $cv_narrative->id]). '">' . $cv_narrative->title . ((!$edit_perm ? '' : ((!$cv_narrative->is_public || !$researcher->is_public) ? ' <i class="fa-solid
                                    fa-eye-slash fa-xs"></i>' : ' <i class="fa-solid fa-eye fa-xs"></i>'))) .'</a></li>';
                                    }
                                }
                            ?>
                                    </ul>

                                </li>
                        <?php endif; ?>

                    </ul>
                </div>
                */ ?>
            </div>

            <?php
                echo Html::hiddenInput('template_id', $template->id, ['id' => 'template_id']);
                $indicatorIndex = 0;
                $listIds = array_keys($contributions_indicators);
                echo '<div id="scholar-profile">';
                echo '<div id="profile-toc-wrap">';
                echo '<div class="sidebar"><div class="sidebar-body"><ul id="profile-toc"></ul></div></div>';
                echo '<div class="main-content" id="profile-content">';

                foreach ($template_elements as $index => $element) {
                    switch ($element['type']) {
                        case 'Facets':
                            // optionally pick a contributions list's facets (first one, or match by element ID if needed)
                            //$linked_id = $element['config']['linked_contribution_element_id'] ?? null;
                            $linked_id = null;

                            foreach ($element['config'] as $section) {
                                if (is_array($section) && isset($section['linked_contribution_element_id'])) {
                                    $linked_id = $section['linked_contribution_element_id'];
                                    break;
                                }
                            }

                            if (! isset($contributions_lists[$linked_id])) {
                                echo "<div class='text-danger'>Missing result for linked list ID: ${linked_id}</div>";
                                break;
                            }

                            $selected = $contributions_selected_filters[$linked_id] ?? [];

                            $isLinkedUserDefined = false;

                            foreach ($template_elements as $te2) {
                                if (($te2['type'] ?? null) === 'Contributions List' && ($te2['element_id'] ?? null) == $linked_id) {
                                    $isLinkedUserDefined = ! empty($te2['config']['user_defined']) && (int) $te2['config']['user_defined'] === 1;
                                    break;
                                }
                            }

                            $lr = $contributions_lists[$linked_id] ?? null;
                            $hasLinkedSelection = $lr && (
                                (! empty($lr['selected_papers']) && count($lr['selected_papers']) > 0) ||
                                (! empty($lr['selected_papers_num']) && $lr['selected_papers_num'] > 0)
                            );

                            if ($isLinkedUserDefined && ! $hasLinkedSelection) {
                                $contributions_lists[$linked_id]['facets'] = [
                                    'topics' => ['counts' => [], 'options' => []],
                                    'roles' => ['counts' => [], 'options' => []],
                                    'accesses' => ['counts' => [], 'options' => []],
                                    'types' => ['counts' => [], 'options' => []],
                                ];
                                $contributions_lists[$linked_id]['papers'] = [];
                                $contributions_lists[$linked_id]['papers_num'] = 0;
                            }

                            // Build margin style from config _margins
                            $marginStyle = '';

                            if (! empty($element['config']['_margins']['margin_top'])) {
                                $marginStyle .= 'margin-top: ' . $element['config']['_margins']['margin_top'] . '; ';
                            }

                            if (! empty($element['config']['_margins']['margin_right'])) {
                                $marginStyle .= 'margin-right: ' . $element['config']['_margins']['margin_right'] . '; ';
                            }

                            if (! empty($element['config']['_margins']['margin_bottom'])) {
                                $marginStyle .= 'margin-bottom: ' . $element['config']['_margins']['margin_bottom'] . '; ';
                            }

                            if (! empty($element['config']['_margins']['margin_left'])) {
                                $marginStyle .= 'margin-left: ' . $element['config']['_margins']['margin_left'] . '; ';
                            }
                            ?>
                            <div id="facets-<?= $element['element_id'] ?>"<?= ! empty($marginStyle) ? ' style="' . $marginStyle . '"' : '' ?>>
                                <?php
                            echo FacetsItem::widget([
                                'edit_perm' => $edit_perm,
                                'result' => $contributions_lists[$linked_id],
                                'formId' => 'scholar-form',
                                'selected_topics' => $selected['topics'] ?? [],
                                'selected_roles' => $selected['roles'] ?? [],
                                'selected_accesses' => $selected['accesses'] ?? [],
                                'selected_types' => $selected['types'] ?? [],
                                'current_cv_narrative' => null,
                                'researcher' => $researcher,
                                'element_config' => $element['config'],
                                'selected_per_list' => $selected_per_list,
                                'facets_linked_to_lists' => $facets_linked_to_lists,
                                'facet_element_id' => $element['element_id'],
                            ]);
                            ?>
                            </div>
                            <?php
                            break;

                        case 'Indicators':

                            $indicator_items = $element['config'];
                            $linked_list_id = $indicator_items[0]['linked_contribution_element_id'] ?? null;

                            if (! $linked_list_id || ! isset($contributions_indicators[$linked_list_id])) {
                                echo "<div class='text-danger'> No indicators found for linked list ID: ${linked_list_id}</div>";
                                break;
                            }

                            $indicators_local = $contributions_indicators[$linked_list_id];

                            // Check if the linked Contributions List is user-defined
                            $isLinkedUserDefined = false;

                            foreach ($template_elements as $te2) {
                                if (($te2['type'] ?? null) === 'Contributions List' && ($te2['element_id'] ?? null) == $linked_list_id) {
                                    $isLinkedUserDefined = ! empty($te2['config']['user_defined']) && (int) $te2['config']['user_defined'] === 1;
                                    break;
                                }
                            }

                            // See if that list already has saved selections
                            $lr = $contributions_lists[$linked_list_id] ?? null;
                            $hasLinkedSelection = $lr && (
                                (! empty($lr['selected_papers']) && count($lr['selected_papers']) > 0) ||
                                (! empty($lr['selected_papers_num']) && (int) $lr['selected_papers_num'] > 0)
                            );

                            // If user-defined and no selection → use an "empty" indicators payload
                            if ($isLinkedUserDefined && ! $hasLinkedSelection) {
                                // find the linked Contributions List config to respect its admin toggles
                                $linkedListShowMissing = true;

                                foreach ($template_elements as $te2) {
                                    if (($te2['type'] ?? null) === 'Contributions List' && ($te2['element_id'] ?? null) == $linked_list_id) {
                                        $linkedListShowMissing = isset($te2['config']['show_missing_papers']) ? (bool) $te2['config']['show_missing_papers'] : true;
                                        break;
                                    }
                                }
                                $indicators_local = [
                                    'works_num' => 0,
                                    'missing_papers_num' => count($missing_papers ?: []),
                                    'show_missing_papers' => $linkedListShowMissing,
                                    'popular_works_count' => 0,
                                    'influential_works_count' => 0,
                                    'citations_num' => 0,
                                    'popularity' => ['number' => 0, 'exponent' => 'e0'],
                                    'influence' => ['number' => 0, 'exponent' => 'e0'],
                                    'impulse' => 0,
                                    'h_index' => 0,
                                    'i10_index' => 0,
                                    'academic_age' => '-',
                                    'responsible_academic_age' => '-',
                                    'paper_min_year' => 0,
                                    'work_types_num' => [
                                        'papers' => 0,
                                        'datasets' => 0,
                                        'software' => 0,
                                        'other' => 0,
                                    ],
                                    'openness' => [],
                                ];
                            } else {
                                $indicators_local = $contributions_indicators[$linked_list_id];
                            }

                            // Build margin style from config _margins
                            $marginStyle = '';

                            if (! empty($element['config']['_margins']['margin_top'])) {
                                $marginStyle .= 'margin-top: ' . $element['config']['_margins']['margin_top'] . '; ';
                            }

                            if (! empty($element['config']['_margins']['margin_right'])) {
                                $marginStyle .= 'margin-right: ' . $element['config']['_margins']['margin_right'] . '; ';
                            }

                            if (! empty($element['config']['_margins']['margin_bottom'])) {
                                $marginStyle .= 'margin-bottom: ' . $element['config']['_margins']['margin_bottom'] . '; ';
                            }

                            if (! empty($element['config']['_margins']['margin_left'])) {
                                $marginStyle .= 'margin-left: ' . $element['config']['_margins']['margin_left'] . '; ';
                            }
                            ?>
                            <div id="indicators-list-<?= $element['element_id'] ?>"<?= ! empty($marginStyle) ? ' style="' . $marginStyle . '"' : '' ?>>
                                <?php
                            echo IndicatorsItem::widget([
                                'edit_perm' => $edit_perm,
                                'works_num' => $indicators_local['works_num'] ?? 0,
                                'missing_papers_num' => $indicators_local['missing_papers_num'] ?? 0,
                                'show_missing_works' => $indicators_local['show_missing_papers'] ?? true,
                                'popular_works_count' => $indicators_local['popular_works_count'] ?? 0,
                                'influential_works_count' => $indicators_local['influential_works_count'] ?? 0,
                                'citations' => $indicators_local['citations_num'] ?? 0,
                                'popularity' => $indicators_local['popularity'] ?? ['number' => 0, 'exponent' => 'e0'],
                                'influence' => $indicators_local['influence'] ?? ['number' => 0, 'exponent' => 'e0'],
                                'impulse' => $indicators_local['impulse'] ?? 0,
                                'h_index' => $indicators_local['h_index'] ?? 0,
                                'i10_index' => $indicators_local['i10_index'] ?? 0,
                                'academic_age' => $indicators_local['academic_age'] ?? '',
                                'responsible_academic_age' => $indicators_local['responsible_academic_age'] ?? '',
                                'paper_min_year' => $indicators_local['paper_min_year'] ?? 0,
                                'papers_num' => $indicators_local['work_types_num']['papers'] ?? 0,
                                'datasets_num' => $indicators_local['work_types_num']['datasets'] ?? 0,
                                'software_num' => $indicators_local['work_types_num']['software'] ?? 0,
                                'other_num' => $indicators_local['work_types_num']['other'] ?? 0,
                                'openness' => $indicators_local['openness'] ?? [],
                                'facets_selected' => $facets_selected,
                                'rag_data' => $rag_data,
                                'element_config' => $element['config'],
                            ]);
                            ?>
                            </div>
                            <?php
                            break;

                        case 'Contributions List':
                            $list_id = $element['element_id'];
                            $element_id = $element['element_id'];

                            $list_result = $contributions_lists[$list_id] ?? [
                                'papers' => [],
                                'papers_num' => 0,
                                'facets' => [],
                            ];
                            // decide if user can select works for this list
                            $canUserSelect = ! empty($element['config']['user_defined']) && (int) $element['config']['user_defined'] === 1 && $edit_perm;
                            $maxUserSelect = isset($element['config']['user_defined_max']) && $element['config']['user_defined_max'] !== ''
                                ? (int) $element['config']['user_defined_max']
                                : null;
                            // Always define a default for visible_papers
                            $visible_papers = $list_result['papers'] ?? [];

                            // If it's user-defined and there is no saved selection, hide the list contents
                            if ($canUserSelect) {
                                $hasUserSelection = (
                                    (isset($list_result['selected_papers_num']) && (int) $list_result['selected_papers_num'] > 0) ||
                                    (! empty($list_result['selected_papers']) && count($list_result['selected_papers']) > 0)
                                );

                                if (! $hasUserSelection) {
                                    $visible_papers = [];
                                }
                            }

                            if ($canUserSelect) {
                                // Add Select Works button and modal trigger

                                $selectWorksBtnHtml = Html::button('<i class="fa fa-check-square-o"></i> Select Works', [
                                    'class' => 'btn btn-custom-color',
                                    'style' => 'margin-bottom:10px;',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#select-works-modal-' . $list_id,
                                    'data-max' => $maxUserSelect,
                                ]);

                                $footer = '
                                    <button class="btn btn-success save-selected-works" 
                                            data-list-id="' . $list_id . '">Save</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                ';

                                Modal::begin([
                                    'header' => '
                                        <div class="clearfix">
                                            <h4 class="modal-title pull-left">Select Works for This List</h4>
                                            <div class="pull-right">
                                                <small class="text-muted selection-counter"
                                                    id="selection-counter-' . $list_id . '"
                                                    data-list-id="' . $list_id . '"
                                                    style="display:none;"></small>
                                                <span aria-hidden="true">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                            </div>
                                        </div>
                                    ',
                                    'id' => 'select-works-modal-' . $list_id,
                                    'size' => 'modal-lg',
                                    'footer' => $footer
                                ]); ?>
                                    <?php
                                    // collect already-saved IDs for this list
                                    $serverSelectedIds = [];

                                if (! empty($list_result['selected_papers']) && is_array($list_result['selected_papers'])) {
                                    foreach ($list_result['selected_papers'] as $item) {
                                        if (is_array($item)) {
                                            $id = $item['internal_id'] ?? $item['id'] ?? null;

                                            if ($id === null) {
                                                foreach ($item as $v) {
                                                    if (! is_array($v)) {
                                                        $id = $v;
                                                        break;
                                                    }
                                                }
                                            }
                                        } else {
                                            $id = $item;
                                        }

                                        if ($id !== null && $id !== '') {
                                            $serverSelectedIds[] = (string) $id;
                                        }
                                    }
                                } ?>

                                    <?= Html::hiddenInput('selected_papers_' . $list_id, implode(',', $serverSelectedIds), [
                                        'id' => 'selected_papers_' . $list_id,
                                        'required' => true
                                    ]) ?>

                                    <?php
                                    // Use GridView like in CV Narrative, but with your papers
                                    echo GridView::widget([
                                        'id' => 'papers-selection-grid-' . $list_id,
                                        'dataProvider' => new yii\data\ArrayDataProvider([
                                            'allModels' => $list_result['all_papers'] ?? $list_result['papers'],
                                            'pagination' => false,
                                        ]),
                                        'layout' => "<div style='overflow-y:auto; max-height:500px'>{items}</div>",
                                        'tableOptions' => [
                                            'class' => 'table table-striped'
                                        ],
                                        'columns' => [
                                            [
                                                'class' => 'yii\grid\CheckboxColumn',
                                                'name' => 'papers-selection[]',
                                                'contentOptions' => ['class' => 'papers-checkbox-column'],
                                                'headerOptions' => ['class' => 'papers-checkbox-column'],
                                                'checkboxOptions' => function ($model) use ($serverSelectedIds) {
                                                    $id = $model['internal_id'];

                                                    return [
                                                        'class' => 'papers-selection-checkbox green-checkbox',
                                                        'data-key' => $id,
                                                        'checked' => in_array($id, $serverSelectedIds, true),
                                                    ];
                                                },
                                                'header' => Html::checkbox('select-all', false, [
                                                    'class' => 'papers-select-on-check-all'
                                                ]),
                                            ],

                                            [
                                                'label' => Html::tag('span', 'Select All', ['class' => 'text-muted select-all-toggle-label', 'data-list-id' => $list_id, 'style' => 'font-weight: normal;']),
                                                'encodeLabel' => false,
                                                'format' => 'raw',
                                                'value' => function ($data) {
                                                    $row = Html::beginTag('div', ['class' => 'article-info']);
                                                    $row .= Html::tag('div', Html::tag('b', empty($data['title']) ? 'N/A' : $data['title']));
                                                    $row .= Html::beginTag('div');
                                                    $row .= Html::tag('i', (empty($data['journal']) ? 'N/A' : $data['journal']) . ' · ');
                                                    $row .= Html::tag('i', empty($data['year']) ? 'N/A' : $data['year']);
                                                    $row .= Html::endTag('div');
                                                    $row .= Html::endTag('div');

                                                    return $row;
                                                },
                                            ],
                                        ],
                                    ]); ?>

                                <?php Modal::end(); ?>
                                <?php
                                $hasUserSelection = (
                                        (isset($list_result['selected_papers_num']) && (int) $list_result['selected_papers_num'] > 0) ||
                                    (! empty($list_result['selected_papers']) && count($list_result['selected_papers']) > 0)
                                    );

                                $shouldHidePapers = ($canUserSelect && ! $hasUserSelection);
                                $visible_papers = $shouldHidePapers ? [] : ($list_result['papers'] ?? []); ?>
                            <?php
                            }
                            ?>
                        <?php
                            $selected = $contributions_selected_filters[$list_id] ?? [];
                            $facets_for_this_list = $facets_linked_to_lists[$element_id] ?? null;

                            // Create no-works message if user can select but hasn't selected any works
                            $noWorksMessage = '';

                            if ($canUserSelect && ! $hasUserSelection) {
                                $noWorksMessage = Html::tag('div', 'No works selected yet for this list.', [
                                    'class' => 'alert alert-warning text-center no-works-alert',
                                    'role' => 'alert',
                                ]);
                            }

                            // Build margin style
                            $marginStyle = '';

                            if (! empty($element['config']['margin_top'])) {
                                $marginStyle .= 'margin-top: ' . $element['config']['margin_top'] . '; ';
                            }

                            if (! empty($element['config']['margin_right'])) {
                                $marginStyle .= 'margin-right: ' . $element['config']['margin_right'] . '; ';
                            }

                            if (! empty($element['config']['margin_bottom'])) {
                                $marginStyle .= 'margin-bottom: ' . $element['config']['margin_bottom'] . '; ';
                            }

                            if (! empty($element['config']['margin_left'])) {
                                $marginStyle .= 'margin-left: ' . $element['config']['margin_left'] . '; ';
                            }

                        ?>
                            <div id="contributions-list-<?= $list_id ?>"<?= ! empty($marginStyle) ? ' style="' . $marginStyle . '"' : '' ?>>
                                <?php
                                    // Determine sort field: if top_k is set, use config value; otherwise check GET parameter (list-specific)
                                    $currentSortField = ! empty($element['config']['top_k'])
                                        ? ($element['config']['sort'] ?? 'year')
                                        : Yii::$app->request->get('sort_' . $list_id, $element['config']['sort'] ?? 'year');

                                    echo ContributionsListItem::widget([
                                        'impact_indicators' => $impact_indicators,
                                        'edit_perm' => $edit_perm,
                                        'facets_selected' => ! empty($list_result['facets']),
                                        'result' => $list_result,
                                        'papers' => $visible_papers,
                                        'works_num' => count($visible_papers),
                                        'missing_papers' => $missing_papers,
                                        'missing_papers_num' => count($missing_papers ?: []),
                                        'list_id' => $list_id,
                                        'show_missing_works' => $element['config']['show_missing_papers'] ?? true,
                                        'sort_field' => $currentSortField,
                                        'orderings' => [
                                            'year' => 'Publication year',
                                            'influence' => 'Influence',
                                            'popularity' => 'Popularity',
                                            'impulse' => 'Impulse',
                                            'citation_count' => 'Citation Count'
                                        ],
                                        'formId' => 'scholar-form',
                                        'current_cv_narrative' => null,
                                        'element_config' => $element['config'],
                                        'facets_for_this_list' => $facets_for_this_list,
                                        'selected_topics' => $selected['topics'] ?? [],
                                        'selected_tags' => $selected['tags'] ?? [],
                                        'selected_roles' => $selected['roles'] ?? [],
                                        'selected_accesses' => $selected['accesses'] ?? [],
                                        'selected_types' => $selected['types'] ?? [],
                                        'preHeaderHtml' => $canUserSelect ? $selectWorksBtnHtml : '',
                                        'show_pagination' => ! empty($element['config']['show_pagination']),
                                        'noWorksMessage' => $noWorksMessage,
                                        'profile_owner_user_id' => $researcher->user_id ?? null,
                                    ]);
                                ?>
                            </div>
                        <?php
                            break;

                        case 'Narrative':
                            // Build margin style
                            $marginStyle = '';

                            if (! empty($element['config']->margin_top)) {
                                $marginStyle .= 'margin-top: ' . $element['config']->margin_top . '; ';
                            }

                            if (! empty($element['config']->margin_right)) {
                                $marginStyle .= 'margin-right: ' . $element['config']->margin_right . '; ';
                            }

                            if (! empty($element['config']->margin_bottom)) {
                                $marginStyle .= 'margin-bottom: ' . $element['config']->margin_bottom . '; ';
                            }

                            if (! empty($element['config']->margin_left)) {
                                $marginStyle .= 'margin-left: ' . $element['config']->margin_left . '; ';
                            }
                            ?>
                            <div id="narrative-<?= $element['element_id'] ?>"<?= ! empty($marginStyle) ? ' style="' . $marginStyle . '"' : '' ?>>
                                <?php
                                    echo NarrativeElement::widget([
                                        'index' => $index,
                                        'element_id' => $element['element_id'],
                                        'title' => $element['config']->title,
                                        'heading_type' => $element['config']->heading_type,
                                        'description' => $element['config']->description,
                                        'tip' => $element['config']->tip,
                                        'hide_when_empty' => $element['config']->hide_when_empty,
                                        'edit_perm' => $edit_perm,
                                        'value' => $element['config']->value,
                                        'limit_value' => $element['config']->limit_value,
                                        'limit_type' => $element['config']->limit_type,
                                        'last_updated' => $element['config']->last_updated,
                                        'messages' => $element['messages'],
                                    ]);
                                ?>
                            </div>
                            <?php
                            break;

                        case 'Dropdown':
                            // Build margin style
                            $marginStyle = '';

                            if (! empty($element['config']->margin_top)) {
                                $marginStyle .= 'margin-top: ' . $element['config']->margin_top . '; ';
                            }

                            if (! empty($element['config']->margin_right)) {
                                $marginStyle .= 'margin-right: ' . $element['config']->margin_right . '; ';
                            }

                            if (! empty($element['config']->margin_bottom)) {
                                $marginStyle .= 'margin-bottom: ' . $element['config']->margin_bottom . '; ';
                            }

                            if (! empty($element['config']->margin_left)) {
                                $marginStyle .= 'margin-left: ' . $element['config']->margin_left . '; ';
                            }
                            ?>
                            <div id="dropdown-<?= $element['element_id'] ?>"<?= ! empty($marginStyle) ? ' style="' . $marginStyle . '"' : '' ?>>
                                <?php
                                    echo DropdownElement::widget([
                                        'index' => $index,
                                        'edit_perm' => $edit_perm,
                                        'element_id' => $element['element_id'],
                                        'title' => $element['config']->title,
                                        'heading_type' => $element['config']->heading_type,
                                        'description' => $element['config']->description,
                                        'hide_when_empty' => $element['config']->hide_when_empty,
                                        'elementDropdownOptionsArray' => ArrayHelper::map($element['config']->elementDropdownOptions, 'id', 'option_name'),
                                        'option_id' => $element['config']->option_id,
                                        'last_updated' => $element['config']->last_updated,
                                    ]);
                                ?>
                            </div>
                            <?php
                            break;

                        case 'Section Divider':
                            echo SectionDivider::widget([
                                'index' => $index,
                                'element_id' => $element['element_id'],
                                'title' => $element['config']->title,
                                'heading_type' => $element['config']->heading_type,
                                'description' => $element['config']->description,
                                'show_description_tooltip' => $element['config']->show_description_tooltip,
                                'top_padding' => $element['config']->top_padding,
                                'bottom_padding' => $element['config']->bottom_padding,
                                'show_top_hr' => $element['config']->show_top_hr,
                                'show_bottom_hr' => $element['config']->show_bottom_hr,
                                'margin_top' => $element['config']->margin_top ?? null,
                                'margin_right' => $element['config']->margin_right ?? null,
                                'margin_bottom' => $element['config']->margin_bottom ?? null,
                                'margin_left' => $element['config']->margin_left ?? null,
                                'edit_perm' => $edit_perm,
                            ]);
                            break;
                        case 'Bulleted List':
                            // Build margin style
                            $marginStyle = '';

                            if (! empty($element['config']->margin_top)) {
                                $marginStyle .= 'margin-top: ' . $element['config']->margin_top . '; ';
                            }

                            if (! empty($element['config']->margin_right)) {
                                $marginStyle .= 'margin-right: ' . $element['config']->margin_right . '; ';
                            }

                            if (! empty($element['config']->margin_bottom)) {
                                $marginStyle .= 'margin-bottom: ' . $element['config']->margin_bottom . '; ';
                            }

                            if (! empty($element['config']->margin_left)) {
                                $marginStyle .= 'margin-left: ' . $element['config']->margin_left . '; ';
                            }
                            ?>
                            <div id="bulleted-list-<?= $element['element_id'] ?>"<?= ! empty($marginStyle) ? ' style="' . $marginStyle . '"' : '' ?>>
                                <?php
                                    echo BulletedList::widget([
                                        'element_id' => $element['element_id'],
                                        'title' => $element['config']->title,
                                        'heading_type' => $element['config']->heading_type,
                                        'description' => $element['config']->description,
                                        'elements_number' => $element['config']->elements_number,
                                        'items' => $element['config']->items,
                                        'edit_perm' => $edit_perm,
                                    ]);
                                ?>
                            </div>
                            <?php
                            break;

                        case 'Table':
                            // Build margin style
                            $marginStyle = '';

                            if (! empty($element['config']->margin_top)) {
                                $marginStyle .= 'margin-top: ' . $element['config']->margin_top . '; ';
                            }

                            if (! empty($element['config']->margin_right)) {
                                $marginStyle .= 'margin-right: ' . $element['config']->margin_right . '; ';
                            }

                            if (! empty($element['config']->margin_bottom)) {
                                $marginStyle .= 'margin-bottom: ' . $element['config']->margin_bottom . '; ';
                            }

                            if (! empty($element['config']->margin_left)) {
                                $marginStyle .= 'margin-left: ' . $element['config']->margin_left . '; ';
                            }
                            ?>
                            <div id="table-<?= $element['element_id'] ?>"<?= ! empty($marginStyle) ? ' style="' . $marginStyle . '"' : '' ?>>
                                <?php
                                    echo TableElement::widget([
                                        'edit_perm' => $edit_perm,
                                        'element_id' => $element['element_id'],
                                        'title' => $element['config']->title,
                                        'description' => $element['config']->description,
                                        'heading_type' => $element['config']->heading_type,
                                        'hide_when_empty' => $element['config']->hide_when_empty,
                                        'max_rows' => $element['config']->max_rows,
                                        'table_headers' => ArrayHelper::map($element['config']->elementTableHeaders, 'header_name', 'header_width'),
                                        'table_data' => $element['config']->table_data,
                                        'last_updated' => $element['config']->last_updated,
                                    ]);
                                ?>
                            </div>
                            <?php
                            break;

                        default:
                            break;
                    }
                }
                echo '</div>'; // end main-content
                echo '</div>'; // end profile-toc-wrap
                echo '</div>'; // end scholar-profile
            ?>
        </div>


    <?php endif; ?>

    <?php /*
        <?php if ($edit_perm): ?>

            <?php
                $footer = '
                <button class="btn btn-success" type="submit" name="submit" value="Submit" form="cv-narrative-form">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                ';
                Modal::begin([
                    'header' => "<h4><span id='cv-narrative-modal-header'>New</span> Narrative</h4>",
                    'id' => 'new-cv-narrative-modal',
                    'size' => 'modal-lg',
                    'footer' => $footer
            ]);?>

                <form id="cv-narrative-form" autocomplete="off" method="POST" action="<?= Url::to(['scholar/save-cv-narrative'])?>">

                    <?= Html::hiddenInput('new_cv_narrative_id', '', ['id' => 'new_cv_narrative_id']) ?>

                    <div class="form-group bip-focus">
                        <label for="new_cv_narrative_title">Heading:</label>
                        <input id="new_cv_narrative_title" name="new_cv_narrative_title" class="form-control" maxlength="100" required="true"/>
                    </div>
                    <div class="form-group">
                        <label for="new_cv_narrative_description">Narrative:</label>
                        <textarea id="new_cv_narrative_description" name="new_cv_narrative_description"></textarea>
                    </div>

                    <?= Html::hiddenInput('new_cv_narrative_selected_papers', '', ['id' => 'new_cv_narrative_selected_papers', 'required'=>'true']) ?>
                </form>

                <?php Pjax::begin([
                    'enablePushState' => false,
                    'enableReplaceState' => false,
                    'timeout' => false,
                    'id' => 'cv-narrative-works-container',
                    'options' => [
                        'class' => 'pjax-modal',
                    ],
                ]); ?>



                    <?php
                        // calculate the current sorting attribute of the gridview
                        $sorting_attribute = $cv_narrative_works->sort->attributeOrders;

                        $sorting_field = ucfirst(current(array_keys($sorting_attribute)));

                        $sorting_direction_icon = current($sorting_attribute) === SORT_ASC ? '<i class="fa-solid fa-arrow-up"></i>' : '<i class="fa-solid fa-arrow-down"></i>';
                    ?>
                    <?= GridView::widget([
                        'id' => 'cv-narrative-grid-view',
                        'dataProvider' => $cv_narrative_works,
                        'layout' =>
                            "<div class='row'>
                                <div class='col-md-4 col-xs-12'>
                                    <div class='dropdown inline-block-d' style = 'margin-bottom:5px'>
                                    <button class='btn dropdown-toggle my-btn-dropdown' type='button' data-toggle='dropdown'>$sorting_direction_icon $sorting_field  <span class='caret'></span></button>
                                    {sorter}</div>
                                </div>\n
                                <div class='text-center col-md-4 col-xs-12'>
                                    {pager}
                                </div>\n
                                <div class='col-xs-12'>
                                    {summary}
                                </div>
                            </div>\n
                            <div style='
                            overflow-y: auto;
                            max-height: 500px;
                            '>
                            {items}
                            </div>",
                        'tableOptions' => [
                            'class' => 'table table-striped'
                        ],
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                                'name' => 'cv-narrative-selection[]',
                                'contentOptions' => ['class' => 'cv-narrative-checkbox-column'],
                                'headerOptions' => ['class' => 'cv-narrative-checkbox-column'],
                                'header' => Html::checkBox('cv-narrative-selection_all', false, ['class' => 'cv-narrative-select-on-check-all green-checkbox']),
                                'checkboxOptions' => function ($model, $key, $index, $column) {
                                    return ['class' => 'cv-narrative-selection-checkbox green-checkbox', 'value' => $key, 'data-key' => $model['internal_id']];
                                },
                            ],
                            [
                                'label'=>'Work',
                                'format' => 'raw',
                                'value' => function ($data) {;
                                    $row  =  Html::beginTag('div', ['class' => 'article-info']);
                                    $row .=  Html::tag('div', Html::tag('b', empty($data['title']) ? "N/A" : $data['title']));
                                    $row .=  Html::beginTag('div');
                                    $row .=  Html::tag('i', (empty($data['journal']) ? "N/A" : $data['journal']) . ' &middot ');
                                    $row .=  Html::tag('i', empty($data['year']) ? "N/A" : $data['year']);
                                    $row .=  Html::endTag('div');
                                    $row .=  Html::endTag('div');
                                    return $row;
                                },

                            ],


                        ],
                        'pager' => [
                            'options' => ['class' => 'pagination '],
                            'linkOptions' => ['class' => 'page-link'],
                            'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link disabled'],
                            'activePageCssClass' => 'active',
                            'prevPageCssClass' => 'page-item',
                            'nextPageCssClass' => 'page-item',
                            'prevPageLabel' => '<span aria-hidden="true">&laquo;</span>',
                            'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
                            'maxButtonCount' => 5,
                        ],
                        'sorter' => [
                            'options' => ['class' => 'dropdown-menu']
                        ]
                    ]); ?>


                <?php Pjax::end(); ?>
            <?php Modal::end(); ?>

        <?php endif; ?>

    */ ?>

    <!-- Template Feedback Modal -->
    <?php if (! Yii::$app->user->isGuest && $template->isHidden()): ?>
        <?php
        $footer = '
            <button class="btn btn-custom-color" type="button" id="submit-template-feedback-btn">Submit Feedback</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        ';
        Modal::begin([
            'header' => '<h4>Send Feedback to Template Creator</h4>',
            'id' => 'templateFeedbackModal',
            'size' => 'modal-md',
            'footer' => $footer
        ]);
        ?>
            <form id="template-feedback-form" autocomplete="off">
                <?= Html::hiddenInput('template_id', $template->id, ['id' => 'template-feedback-template-id']) ?>
                <?= Html::hiddenInput('profile_orcid', $researcher->orcid, ['id' => 'template-feedback-profile-orcid']) ?>

                <div class="form-group">
                    <label for="template-feedback-message">Feedback <span class="text-danger">*</span></label>
                    <textarea id="template-feedback-message" name="message" class="form-control" rows="4" maxlength="2000" required placeholder="Describe your feedback about this template..."></textarea>
                    <small class="form-text text-muted"><span id="template-feedback-description-count">0</span>/2000 characters</small>
                </div>

                <div id="template-feedback-message-box" class="alert" style="display: none;"></div>
            </form>
        <?php Modal::end(); ?>
    <?php endif; ?>

    <!-- Report Profile Modal -->
    <?php if (! $edit_perm && ! Yii::$app->user->isGuest): ?>
        <?php
        $footer = '
            <button class="btn btn-custom-color" type="button" id="submit-report-btn">Submit Report</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        ';
        Modal::begin([
            'header' => '<h4>Report Profile</h4>',
            'id' => 'reportProfileModal',
            'size' => 'modal-md',
            'footer' => $footer
        ]);
        ?>
            <form id="report-profile-form" autocomplete="off">
                <?= Html::hiddenInput('reported_orcid', $researcher->orcid, ['id' => 'report-profile-orcid']) ?>
                
                <div class="form-group">
                    <label for="report-reason">Reason for Reporting <span class="text-danger">*</span></label>
                    <select id="report-reason" name="reason" class="form-control" required>
                        <option value="" disabled selected>-- Select a reason --</option>
                        <option value="Inappropriate content">Inappropriate content</option>
                        <option value="Fake or impersonation">Fake or impersonation</option>
                        <option value="Spam or misleading">Spam or misleading</option>
                        <option value="Copyright violation">Copyright violation</option>
                        <option value="Harassment or abuse">Harassment or abuse</option>
                        <option value="Other">Other</option>
                    </select>
                    <small class="form-text text-muted">Please select the reason for reporting this profile.</small>
                </div>
                
                <div class="form-group">
                    <label for="report-description">Additional Details (optional)</label>
                    <textarea id="report-description" name="description" class="form-control" rows="4" maxlength="1000" placeholder="Please provide any additional information that may help us review this report..."></textarea>
                    <small class="form-text text-muted"><span id="report-description-count">0</span>/1000 characters</small>
                </div>
                
                <div id="report-message" class="alert" style="display: none;"></div>
            </form>
        <?php Modal::end(); ?>
    <?php endif; ?>
<?php endif; ?>
