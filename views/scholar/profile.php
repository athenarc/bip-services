<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\grid\GridView;;
use app\components\BookmarkIcon;
use app\components\ScholarSidebar;
use app\components\ResultItem;
use app\components\FacetsItem;
use app\components\IndicatorsItem;
use app\components\ContributionsListItem;
use app\components\NarrativeElement;
use app\components\DropdownElement;
use app\components\SectionDivider;
use app\components\BulletedList;
use app\components\TableElement;
use app\components\ScholarNavbar;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;

$this->registerJsFile('@web/js/third-party/chartjs/chart_v4.2.0.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/chartjs_radar.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/countUp/countUp_v2.8.0.umd.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/animateIndicators.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/bootstrap-tagsinput/bootstrap-tagsinput.min.js', ['position' => View::POS_END]);
$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/reading-status.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholar-readings.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholarInvolvement.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/responsibleAcadAge.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/cvNarrative.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/profile_visibility.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholarPdfExport.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/papersSelection.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerCssFile('@web/css/tags.css');
$this->registerCssFile('@web/css/reading-status.css');
$this->registerCssFile('@web/css/scholar-profile.css');
$this->registerCssFile('@web/css/missing-works.css');

$this->title = 'BIP! Services - Scholar';

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/css/bootstrap-select.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.8.1/js/bootstrap-select.js"></script>

<!-- Latest compiled and minified CSS -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css"> -->

<!-- Latest compiled and minified JavaScript -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script> -->

<?php
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
?>

<?php if (!isset($researcher->orcid)): ?>

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
                        <?= Html::a('<i class="fa fa-link" aria-hidden="true"></i> Link with your ORCiD', 'https://orcid.org/oauth/authorize?client_id=' . Yii::$app->params['orcid_client_id'] . '&response_type=code&scope=/authenticate&redirect_uri=' . Url::to(['scholar/profile'], true), ['class'=>'btn btn-custom-color', 'style' => 'white-space: break-spaces']); ?>
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
            <?php ActiveForm::begin(['id' => 'scholar-form', 'options' => ['style' => 'display: inline-block;'], 'method'=>'GET', 'action'=> Url::to(['scholar/profile/'. $researcher->orcid . (isset($template->url_name) ? ("/" . $template->url_name) : "")])]); ?>
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
                                            <a class="grey-link" href="<?= "https://orcid.org/" . $researcher->orcid ?>" target="_blank">
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
                echo Html::hiddenInput("template_id", $template->id, [ 'id' => 'template_id' ]);
                $indicatorIndex = 0;
                $listIds = array_keys($contributions_indicators);
                foreach ($template_elements as $index => $element) {

                    switch ($element["type"]) {
                        case "Facets":
                            // optionally pick a contributions list's facets (first one, or match by element ID if needed)
                            //$linked_id = $element['config']['linked_contribution_element_id'] ?? null;
                            $linked_id = null;
                            foreach ($element['config'] as $section) {
                                if (is_array($section) && isset($section['linked_contribution_element_id'])) {
                                    $linked_id = $section['linked_contribution_element_id'];
                                    break;
                                }
                            }
                            if (!isset($contributions_lists[$linked_id])) {
                                echo "<div class='text-danger'>Missing result for linked list ID: $linked_id</div>";
                                break;
                            }

                            $selected = $contributions_selected_filters[$linked_id] ?? [];

                            echo FacetsItem::widget([
                                'edit_perm' => $edit_perm,
                                'result' =>  $contributions_lists[$linked_id],
                                'formId' => 'scholar-form',
                                'selected_topics' => Yii::$app->request->get('topics'),
                                'selected_roles' => Yii::$app->request->get('roles'),
                                'selected_accesses' => Yii::$app->request->get('accesses'),
                                'selected_types' => Yii::$app->request->get('types'),
                                'current_cv_narrative' => null,
                                'researcher' => $researcher,
                                'element_config' => $element["config"],
                                'selected_per_list' => $selected_per_list, 
                            ]);
                            break;

                        case "Indicators":
                            
                            $indicator_items = $element['config'];
                            $linked_list_id = $indicator_items[0]['linked_contribution_element_id'] ?? null;

                            if (!$linked_list_id || !isset($contributions_indicators[$linked_list_id])) {
                                echo "<div class='text-danger'> No indicators found for linked list ID: $linked_list_id</div>";
                                break;
                            }

                            $indicators_local = $contributions_indicators[$linked_list_id];

                            echo IndicatorsItem::widget([
                                'edit_perm' => $edit_perm,
                                'works_num' => $indicators_local['works_num'] ?? 0,
                                'missing_papers_num' => $indicators_local['missing_papers_num'] ?? 0,
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
                                'element_config' => $element["config"],
                            ]);
                            break;

                        case "Contributions List":
                            $list_id = $element["element_id"];
                            $element_id = $element['element_id'];
                            
                            $list_result = $contributions_lists[$list_id] ?? [
                                'papers' => [],
                                'papers_num' => 0,
                                'facets' => [],
                            ];
                            // Add Select Works button and modal trigger
                            echo Html::button('<i class="fa fa-check-square-o"></i> Select Works', [
                                'class' => 'btn btn-custom-color',
                                'style' => 'margin-bottom:10px;',
                                'data-toggle' => 'modal',
                                'data-target' => '#select-works-modal-' . $list_id
                            ]);

                            $footer = '
                                <button class="btn btn-success save-selected-works" 
                                        data-list-id="' . $list_id . '">Save</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            ';

                            Modal::begin([
                                'header' => "<h4>Select Works for This List</h4>",
                                'id' => 'select-works-modal-' . $list_id,
                                'size' => 'modal-lg',
                                'footer' => $footer
                            ]);
                            ?>

                                <?= Html::hiddenInput('selected_papers_' . $list_id, '', [
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
                                            'checkboxOptions' => function ($model) {
                                                return [
                                                    'class' => 'papers-selection-checkbox green-checkbox',
                                                    'data-key' => $model['internal_id']
                                                ];
                                            },
                                        ],

                                        [
                                            'label' => Html::tag('span', 'Select All', ['class' => 'text-muted', 'style' => 'font-weight: normal;']),
                                            'encodeLabel' => false,
                                            'format' => 'raw',
                                            'value' => function ($data) {
                                                $row  = Html::beginTag('div', ['class' => 'article-info']);
                                                $row .= Html::tag('div', Html::tag('b', empty($data['title']) ? "N/A" : $data['title']));
                                                $row .= Html::beginTag('div');
                                                $row .= Html::tag('i', (empty($data['journal']) ? "N/A" : $data['journal']) . ' · ');
                                                $row .= Html::tag('i', empty($data['year']) ? "N/A" : $data['year']);
                                                $row .= Html::endTag('div');
                                                $row .= Html::endTag('div');
                                                return $row;
                                            },
                                        ],
                                    ],
                                ]);
                                ?>

                            <?php Modal::end(); ?>
                        <?php
                            $selected = $contributions_selected_filters[$list_id] ?? [];
                            $facets_for_this_list = $facets_linked_to_lists[$element_id] ?? null;
                        ?>
                            <div id="contributions-list-<?= $list_id ?>">
                                <?php
                                    echo ContributionsListItem::widget([
                                        'impact_indicators' => $impact_indicators,
                                        'edit_perm' => $edit_perm,
                                        'facets_selected' => !empty($list_result['facets']),
                                        'result' => $list_result,
                                        'papers' => $list_result["papers"],
                                        'works_num' => count($list_result["papers"]),
                                        'missing_papers' => $missing_papers,
                                        'missing_papers_num' => count($missing_papers),
                                        'sort_field' => $element['config']['sort'] ?? 'year',
                                        'orderings' => [
                                            'year' => 'Publication year',
                                            'influence' => 'Influence',
                                            'popularity' => 'Popularity',
                                            'impulse' => 'Impulse',
                                            'citation_count' => 'Citation Count'
                                        ],
                                        'formId' => 'scholar-form',
                                        'current_cv_narrative' => null,
                                        'element_config' => $element["config"],
                                        'facets_for_this_list' => $facets_for_this_list,
                                        'selected_topics' => $selected['topics'] ?? [],
                                        'selected_tags' => $selected['tags'] ?? [],
                                        'selected_roles' => $selected['roles'] ?? [],
                                        'selected_accesses' => $selected['accesses'] ?? [],
                                        'selected_types' => $selected['types'] ?? [],
                                    ]);
                                ?>
                            </div>
                        <?php
                            break;

                        case "Narrative":
                            echo NarrativeElement::widget([
                                'index' => $index,
                                'element_id' => $element["element_id"],
                                'title' => $element["config"]->title,
                                'heading_type' => $element["config"]->heading_type,
                                'description' => $element["config"]->description,    
                                'hide_when_empty' => $element["config"]->hide_when_empty,
                                'edit_perm' => $edit_perm,
                                'value' => $element["config"]->value,
                                'limit_value' => $element["config"]->limit_value,
                                'limit_type' => $element["config"]->limit_type,
                                'last_updated' => $element["config"]->last_updated,
                                'messages' => $element["messages"],
                            ]);
                            break;

                        case "Dropdown":
                            echo DropdownElement::widget([
                                'index' => $index,
                                'edit_perm' => $edit_perm,
                                'element_id' => $element["element_id"],
                                'title' => $element["config"]->title,
                                'heading_type' => $element["config"]->heading_type,
                                'description' => $element["config"]->description,    
                                'hide_when_empty' => $element["config"]->hide_when_empty,
                                'elementDropdownOptionsArray' => ArrayHelper::map($element["config"]->elementDropdownOptions, 'id', 'option_name'),
                                'option_id' => $element["config"]->option_id,
                                'last_updated' => $element["config"]->last_updated,
                            ]);
                            break;
                        
                        case "Section Divider":
                            echo SectionDivider::widget([
                                'index' => $index,
                                'element_id' => $element["element_id"],
                                'title' => $element["config"]->title,
                                'heading_type' => $element["config"]->heading_type,
                                'description' => $element["config"]->description,
                                'show_description_tooltip' => $element['config']->show_description_tooltip,
                                'top_padding' => $element["config"]->top_padding,
                                'bottom_padding' => $element["config"]->bottom_padding,
                                'show_top_hr' => $element["config"]->show_top_hr,
                                'show_bottom_hr' => $element["config"]->show_bottom_hr,
                                'edit_perm' => $edit_perm,
                            ]);
                            break;
                        case "Bulleted List":
                            echo BulletedList::widget([
                                'element_id' => $element["element_id"],
                                'title' => $element["config"]->title,
                                'heading_type' => $element["config"]->heading_type,
                                'description' => $element["config"]->description,
                                'elements_number' => $element["config"]->elements_number,
                                'items' => $element["config"]->items,
                                'edit_perm' => $edit_perm,
                            ]);
                            break;

                        case "Table":
                            echo TableElement::widget([
                                'edit_perm' => $edit_perm,
                                'element_id' => $element["element_id"],
                                'title' => $element["config"]->title,
                                'description' => $element["config"]->description,
                                'heading_type' => $element["config"]->heading_type,
                                'hide_when_empty' => $element["config"]->hide_when_empty,
                                'max_rows' => $element["config"]->max_rows,
                                'table_headers' => ArrayHelper::map($element["config"]->elementTableHeaders, 'header_name', 'header_width'),
                                'table_data' => $element["config"]->table_data,
                                'last_updated'  => $element["config"]->last_updated,
                            ]);
                            break;
                            
                        default:
                            break;
                    }
                }
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
<?php endif; ?>
