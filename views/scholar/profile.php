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
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use app\models\AssessmentFrameworks;

$this->registerJsFile('@web/js/third-party/chartjs/chart_v4.2.0.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/chartjs_radar.js',  ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/countUp/countUp_v2.8.0.umd.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/animateIndicators.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/bootstrap-tagsinput/bootstrap-tagsinput.min.js', ['position' => View::POS_END]);
$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/reading-status.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/favoriteTags.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholar-readings.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholarInvolvement.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/responsibleAcadAge.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/cvNarrative.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/scholarAssessment.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js',  ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/narrativeElement.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

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


<?php if (!isset($researcher->orcid)): ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <h1>
                Welcome to your BIP! Scholar Profile
                </h1>
            </div>
            <div class="well profile">
                <div class="col-xs-12">
                    Link BIP! Scholar with your ORCiD account to allow us access your public ORCID records, your name and ORCiD ID.<br/>
                    Please, also ensure that your ORCiD profile has 'Visibility: Everyone' in your Account Settings, since we rely on the public records of you ORCiD profile to create the contents of your BIP! Scholar profile.
                    <div class="col-xs-12 text-center" style="padding-top: 10px;">
                        <?= Html::a('<i class="fa fa-link" aria-hidden="true"></i> Link with your ORCiD', 'https://orcid.org/oauth/authorize?client_id=' . Yii::$app->params['orcid_client_id'] . '&response_type=code&scope=/authenticate&redirect_uri=' . Url::to(['scholar/profile'], true), ['class'=>'btn btn-success']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif (isset($researcher->orcid)): ?>
    
    <?php $this->title = 'BIP! Scholar - ' . $researcher->name; ?>

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
                        <div class="flex-no-wrap wrap-anywhere" style="justify-content:space-between;align-items:start;">
                            <div class="flex-wrap" style = "margin-right:5px;">
                                <span style="margin-right: 10px;">
                                    <?php if ($researcher->is_public): ?>
                                        <i class="fas fa-lock-open grey-text" title="This profile is publicly visible (you can change this from the settings)." style="font-size: 20px;  bottom: 2px; position:relative;"></i>
                                    <?php else: ?>
                                        <i class="fas fa-lock grey-text" title="This profile is only visible to you (you can change this from the settings)." style="font-size: 20px;  bottom: 2px; position:relative;"></i>
                                    <?php endif; ?>
                                </span>
                                <span style="margin-right: 5px;">
                                    <?= $researcher->name ?>
                                </span>
                                <span>
                                    <small>
                                        <a class="grey-link" href = "<?= "https://orcid.org/" . $researcher->orcid ?>" target="_blank">
                                            <i class="fa-brands fa-orcid" title="ORCiD"></i> <?= $researcher->orcid ?>
                                        </a>
                                    </small>
                                </span>
                            </div>
                            <div class="text-right">
                                <?php ActiveForm::begin(['id' => 'templates-dropdown-form', 'options' => ['style' => 'display: inline-block;'], 'method'=>'POST', 'action'=> Url::to(['scholar/profile/'. $researcher->orcid])]); ?>

                                <span><small>Template:</small></span>
                                <?= Html::dropDownList('template_url_name', $template->url_name, $templateDropdownData, [
                                    'prompt' => 'Select template',
                                    'class' => 'form-control templates-dropdown',
                                    'id' => 'templates-dropdown',
                                    'style' => 'display: inline-block; width: auto;'

                                ]); ?>

                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>
                        <?php if ($edit_perm): ?>
                        <div class="text-right">
                            <a href="<?= Url::to(['site/settings']) ?>"><small><i class="fa fa-gears light-grey-link" aria-hidden="true" title="Settings"></i></small></a>
                            <a href="#"><small><i class="fa fa-file-export light-grey-link" aria-hidden="true" title="Export"></i></small></a>
                        </div>
                        <?php endif; ?>

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

                            <!-- <div style="margin-top: 5px; float:right" id="scholar-assessment-dropdowns">
                                <span class="facet-header grey-text">Perspectives:</span>
                                <?php Html::dropDownList('select-presets', null, $presets,
                                    // ArrayHelper::map(AssessmentFrameworks::find()->all(), 'id', 'name'),
                                    [
                                        'class' => 'scholar-assessment-dropdown facet-row',
                                        'id' => 'presets-dropdown',
                                        'prompt' => 'Select Perspective',
                                    ]);  ?>
                            </div>   -->
                    </ul>
                </div>
                */ ?>
            </div>

            <?php
                echo Html::hiddenInput("template_id", $template->id, [ 'id' => 'template_id' ]);

                foreach ($template_elements as $index => $element) {

                    switch ($element["type"]) {
                        case "Facets":
                            echo FacetsItem::widget([
                                'edit_perm' => $edit_perm,
                                'result' => $result,
                                'formId' => 'scholar-form',
                                'selected_topics' => $selected_topics,
                                'selected_roles' => $selected_roles,
                                'selected_accesses' => $selected_accesses,
                                'selected_types' => $selected_types,
                                'current_cv_narrative' => null,
                                'researcher' => $researcher,
                                'element_config' => $element["config"]
                            ]);

                            break;

                        case "Indicators":
                            echo IndicatorsItem::widget([
                                'edit_perm' => $edit_perm,
                                'works_num' => $result["papers_num"],
                                'missing_papers_num' => count($missing_papers),
                                'facets_selected' => $facets_selected,
                                'popular_works_count' => $popular_works_count,
                                'influential_works_count' => $influential_works_count,
                                'citations' => $citations,
                                'impulse' => $impulse,
                                'h_index' => $h_index,
                                'i10_index' => $i10_index,
                                'academic_age' => $academic_age,
                                'paper_min_year' => $paper_min_year,
                                'responsible_academic_age' => $responsible_academic_age,
                                'rag_data' => $rag_data,
                                'papers_num' => $papers_num,
                                'datasets_num' => $datasets_num,
                                'software_num' => $software_num,
                                'other_num' => $other_num,
                                'openness' => $openness,
                                'current_cv_narrative' => null,
                                'element_config' => $element["config"]
                            ]);

                            break;

                        case "Contributions List":
                            echo ContributionsListItem::widget([
                                'impact_indicators' => $impact_indicators,
                                'edit_perm' => $edit_perm,
                                'facets_selected' => $facets_selected,
                                'result' => $result,
                                'papers' => $result["papers"],
                                'works_num' => $result["papers_num"],
                                'missing_papers' => $missing_papers,
                                'missing_papers_num' => count($missing_papers),
                                'sort_field' => $sort_field,
                                'orderings' => $orderings,
                                'formId' => 'scholar-form',
                                'current_cv_narrative' => null,
                            ]);

                            break;

                        case "Narrative":
                            echo NarrativeElement::widget([
                                'index' => $index,
                                'element_id' => $element["element_id"],
                                'title' => $element["config"]->title,
                                'description' => $element["config"]->description,    
                                'hide_when_empty' => $element["config"]->hide_when_empty,
                                'edit_perm' => $edit_perm,
                                'value' => $element["config"]->value
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