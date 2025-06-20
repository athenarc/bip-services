<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use yii\web\View;
use yii\bootstrap\Modal;
use app\components\CustomBootstrapCheckboxList;
use app\components\CustomBootstrapRadioList;
use app\components\MagicSearchBox;
use app\components\CustomFiltersRadioList;
use app\components\CustomFiltersCheckboxList;
use app\components\ResultItem;
use app\components\TopTopicsItem;
use app\components\CustomBootstrapModal;
use yii\helpers\ArrayHelper;
use app\models\Indicators;

use Yii;

$this->title = 'BIP! Services - Finder';

/* @var $this yii\web\View */
$this->registerJsFile('@web/js/resultsFunctions.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/comparison.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/toggleFiltersSidebar.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/remove_filters.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/beforeSearchFormSubmit.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/filtersFocusOutSubmit.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/tinycolor.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/topicsInResults.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/summarize.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerCssFile('@web/css/tags.css');

$this->registerJsFile('@web/js/third-party/countUp/countUp_v2.8.0.umd.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/indexAnimation.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/indexCarousel.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/animateIndicators.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

// set vars to be used in the view
$keywords = $model->keywords;
$filters_count = $model->count_filters();
$start_year = $model->start_year;
$end_year = $model->end_year;

$in_space = ($space_model->url_suffix !== null && $space_model->url_suffix !== '');
if ($in_space) {
    $spaceColor = $space_model->theme_color;
    $this->registerJs("var spaceColor = '{$spaceColor}';", View::POS_HEAD);
    $this->registerJsFile('@web/js/set_space_colors.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
}
?>

<div class="site-index">
    <div class="jumbotron">
        <h1>
            <?php if(isset($space_model->logo)): ?>
                <?= Html::img($space_model->uploadLogoPath() . $space_model->logo, ['class' => '', 'style' => 'max-height: 150px;max-width: 80%;']) ?>
            <?php else: ?>
                <?= Html::img("@web/img/bip-minimal.png", ['class' => 'img-responsive center-block', /*'width' => 100, 'height' => 89*/]) ?>
            <?php endif; ?>

        </h1>
        <p style = "margin-top:-10px;">
            <?= ($in_space) ? $space_model->display_name : 'Amplifying valuable research' ?>
        </p>
        
        <?php if($in_space): ?>
            <p>
                <small>
                    powered by 
                </small>
                <a href='<?=Url::to(['site/index'])?>' target='_blank'>
                    <?= Html::img("@web/img/bip-minimal.png", ['height' => 30]) ?>
                </a>
            </p>
        <?php endif; ?>

        <?php
            $keywords_params = ['autofocus' => true, 'aria-label'=> 'Search', 'placeholder'=>'Enter keywords to retrieve articles...', 'class'=>'search-box form-control'];
            $fieldOptions = ['template' => "{input}<span class='glyphicon glyphicon-search form-control-feedback'></span>"];

            if( $keywords!='' )
                $keywords_params['value'] = $keywords;

                // loading and some filter validation on form submit happens in beforeSearchFormSubmit.js file
                $form = ActiveForm::begin(['id' => 'search-form', 'method'=>'POST', 'action'=> Url::to(['site/index']), 'options'=>[]]);
            ?>
            <?= Html::hiddenInput('space_url_suffix', $space_model->url_suffix, [ 'id' => 'space_url_suffix']) ?>

            <div class='row'>
                <div class="col-md-8 col-md-offset-2">
                    <div class='has-search'>
                        <?= $form->field($model, 'keywords', $fieldOptions)->input('search', $keywords_params) ?>
                        <!-- class sr-only instead of hidden because of Safari browser -->
                        <input type="submit" class="sr-only" hidefocus="true" tabindex="-1">
                    </div>
                </div>
            </div>
            <div class='row grey-text'>
                <div class='col-xs-12'>
                    <div class = "inline-block-d" style = "margin:0 8px">
                        <?= $form->field($model, 'ordering')->dropdownList([
                            'popularity' => 'Popularity',
                            'influence' => 'Influence',
                            'citation_count' => 'Citation Count',
                            'impulse' => 'Impulse',
                            'year' => 'Year'
                            ],
                            ['onchange' => '$(this).closest(\'form\').submit()',
                            'style' => ['display' => 'inline-block', 'width' => 'auto', 'color' => 'grey'],
                            ]
                        );?>
                    </div>
                </div>
            </div>
            <?php if (!empty($results['rows']) || ($keywords != '')) { ?>
                <div id="collapsible_menu">
                    <div id="search_filters" class="toggled">
                        <a href="#" id="remove_filters">reset to default</a><br/>

                        <?= CustomFiltersRadioList::widget(['id' => 'popularity_filter', 'name' => 'popularity', 'model' => $model, 'form' => $form, 'items' => ["top001" => 'Top 0.01%', 'top01' => 'Top 0.1%', 'top1' => 'Top 1%', "top10" => 'Top 10%', 'all' => 'All']]); ?>

                        <?= CustomFiltersRadioList::widget(['id' => 'influence_filter', 'name' => 'influence', 'model' => $model, 'form' => $form, 'items' => ["top001" => 'Top 0.01%', 'top01' => 'Top 0.1%', 'top1' => 'Top 1%', "top10" => 'Top 10%', 'all' => 'All']]); ?>

                        <?= CustomFiltersRadioList::widget(['id' => 'cc_filter', 'name' => 'cc', 'model' => $model, 'form' => $form, 'items' => ["top001" => 'Top 0.01%', 'top01' => 'Top 0.1%', 'top1' => 'Top 1%', "top10" => 'Top 10%', 'all' => 'All']]); ?>

                        <?= CustomFiltersRadioList::widget(['id' => 'impulse_filter', 'name' => 'impulse', 'model' => $model, 'form' => $form, 'items' => ["top001" => 'Top 0.01%', 'top01' => 'Top 0.1%', 'top1' => 'Top 1%', "top10" => 'Top 10%', 'all' => 'All']]); ?>

                        <?= CustomFiltersCheckboxList::widget(['id' => 'type_filter', 'name' => 'type', 'model' => $model, 'form' => $form, 'items' => array_map(function ($value) {return $value['name'];}, Yii::$app->params['work_types']), 'item_class' => "checkbox checkbox-custom filters-margin"]); ?>

                        <?php if ($in_space): ?>
                            <?= CustomFiltersCheckboxList::widget(['id' => 'space_filter', 'name' => 'provided_by', 'model' => $model, 'form' => $form, 'items' => array_column($space_model->solr_name, 'label', 'value'), 'item_class' => "checkbox checkbox-custom filters-margin"]); ?>
                        <?php endif; ?>

                        <div id="years_form_group" class="form-group">
                            <label>Start Year</label>
                            <?php if (!$start_year) { ?>
                                <input class="search-box form-control" type="number" step="1" name="start_year" id="start_year_input" placeholder="Starting Publication Year" min="1400" max='<?= date("Y") ?>' value="" />
                            <?php } else { ?>
                                <input class="search-box form-control" type="number" step="1" name="start_year" id="start_year_input" placeholder="Starting Publication Year" min="1400" max='<?= date("Y") ?>' value="<?= $start_year ?>"/>
                            <?php } ?>
                            <div class="help-block"></div>

                            <label>End Year</label>
                            <?php if (!$end_year) { ?>
                                <input class="search-box form-control has-error" type="number" step="1" name="end_year" id="end_year_input" placeholder="Ending Publication Year" min="1400" max='<?= date("Y") ?>' value="" />
                            <?php } else { ?>
                                <input class="search-box form-control has-error" type="number" step="1"  name="end_year" id="end_year_input" placeholder="Ending Publication Year" min="1400" max='<?= date("Y") ?>' value="<?= $end_year ?>"/>
                            <?php } ?>
                        </div>
                        <!-- add vertical space -->
                        <div id="years_error" class="help-block alert-danger"></div>
                        <label>Topics <i class="fa fa-question-circle" aria-hidden="true" title="Please start typing to get concept suggestions (suggestions are limited to 10)." style="cursor: pointer;"></i></label>

                        <?= MagicSearchBox::widget(['min_char_to_start' => 1,
                                'expansion' => 'both',
                                'suggestions_num' => 10,
                                'html_params' => ['id' => 'topics_search_box','name'=>'topics','class'=>'form-control search-box', 'placeholder' => "Select Topics"],
                                'ajax_action' => Url::toRoute('auto-complete-concepts'),
                                'selected_elements' => $model->topics
                        ]);?>
                        <input type="hidden" name="clear_all" id="clear_all_input"/>
                    </div>
                    <div id="search_filters_toggle_button">
                        <?php if ($filters_count > 0) { ?>
                            <div data-notifications="<?= $filters_count ?>"></div>
                        <?php } ?>
                        <div class="btn-custom-color filters_button left">
                            <div class="rotate flex-no-wrap flex-column items-center">
                                <div class="verticaltext">FILTERS</div>
                                <div id="collapse_filters_button" class="triangle toggled"></div>
                            </div>
                        </div>
                    </div>

                </div>
            <?php } ?>
            <?php ActiveForm::end(); ?>

            <?php if (!empty($researcher_count)): ?>
                <div id="researcher_panel" class="panel-body text-left grey-text" style="font-size: 1.2em;">
                    Searching for a researcher? Found
                    <a class="main-green" href="<?= Url::to([
                        '/scholar/search',
                        'keywords' => $keywords,
                        'ordering' => 'name'
                    ]) ?>">
                        <?= $researcher_count ?> researcher profile<?= $researcher_count > 1 ? 's' : '' ?>
                    </a>
                    matching this query.
                </div>
            <?php endif; ?>

            <a href='<?=Url::to(['site/comparison'])?>' target='_blank' id='comparison' class='btn btn-warning'></a>
            <div id='clear-comparison' onclick="clearSelected();">
                Clear all<i class="fa fa-times" aria-hidden="true"></i>
            </div>

            <div class='row'>
                <div id="loading_results">
                    <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <br/><br/>
                    Loading results (it may take a couple of seconds)...
                </div>
            </div>

            <?php if(!empty($results['rows'])) { ?>
                <div class='container-fluid'>
                    
                    <?= TopTopicsItem::widget([]) ?>

                    <div id="results_hdr" class='row'>
                        <div class='col-sm-12 col-md-3 text-center results-header' style="margin-bottom: 15px;">
                            <?= Yii::$app->formatter->asDecimal($results['pagination']->totalCount, 0) ?> results (<?= Yii::$app->formatter->asDecimal($results['pagination']->pageCount,0) ?> pages)
                        </div>
                        <div class='col-sm-12 col-md-6 text-center' style="margin-bottom: 15px;">
                            <?= LinkPager::widget(['pagination'=>$results['pagination'],
                                'maxButtonCount'=>5,
                                'firstPageLabel' => '<i class="fa-solid fa-backward-fast"></i>',
                                'lastPageLabel'  => '<i class="fa-solid fa-forward-fast"></i>']);
                            ?>
                        </div>
                        <div class='col-sm-12 col-md-3 text-center' style="margin-bottom: 15px;">
                            <button id="summarizeBtn" class="btn btn-default btn-sm" 
                                    data-paper-ids='<?= json_encode(array_map(function($result) { 
                                        return $result['internal_id']; 
                                    }, $results['rows'])) ?>'
                                    data-keywords='<?= $keywords ?>'
                                >
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Summarize top results
                            </button>
                        </div>
                    </div>

                    <div id="summary_panel" class="collapse row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div id="summaryContent" class="grey-text">
                                        <div class="summary-controls">
                                            <div id="regenerate-summary-box" class="regenerate-summary-box" style="display: none;">
                                                <label for="summary-count" class="regenerate-label">Use top</label>
                                                <input type="number" id="summary-count" class="regenerate-input" />
                                                <label for="summary-count" class="regenerate-label">results.</label>
                                                <span 
                                                    role="button" 
                                                    data-toggle="popover" 
                                                    data-placement="auto" 
                                                    title="AI Summary" 
                                                    data-content="<p>The summary format will change based on the selected number of papers:</p>
                                                    <ul>
                                                        <li>1-5 papers: Produces a concise overview.</li>
                                                        <li>6-20 papers: Creates a more detailed, literature review-style summary.</li>
                                                    </ul>
                                                    "
                                                    style="cursor: pointer;"
                                                > 
                                                    <small><i class="fa fa-info-circle light-grey-link" aria-hidden="true"></i></small>
                                                </span>

                                                <button id="regenerate-summary-btn" class="btn btn-sm btn-custom-color regenerate-button">Summarize</button>
                                            </div>
                                            <div class="text-right" id="copy-summary-wrapper" style="display: none;">
                                                <a id="copy-summary-btn" class="btn btn-default btn-xs fs-inherit grey-link" role="button" data-toggle="tooltip">
                                                    <i class="fa fa-copy" aria-hidden="true"></i> Copy to clipboard
                                                </a>
                                            </div>
                                        </div>
                                        <div id="summaryLoading" class="text-center summary-loading-centered">
                                            <i class="fa fa-spinner fa-spin"></i> Generating summary...
                                        </div>
                                        <div id="summaryText" style="text-align: justify; display: none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id='results_tbl' class='row'>
                        <div class='col-md-12'>
                            <?php foreach($results['rows'] as $result ) {
                                echo ResultItem::widget([
                                    "impact_indicators" => $impact_indicators,
                                    "internal_id" => $result["internal_id"],
                                    "doi" => $result["doi"],
                                    "dois_num" => $result["dois_num"],
                                    "openaire_id" => $result["openaire_id"],
                                    "title" => $result["title"],
                                    "authors" => $result["authors"],
                                    "journal" => $result["journal"],
                                    "year" => $result["year"],
                                    "concepts" => $result["concepts"],
                                    "annotations" => $result["annotations"] ?? null,
                                    "relations" => $result["relations"],
                                    "user_id" => $result["user_id"],
                                    "pop_score" => $result["attrank"],
                                    "inf_score" => $result["pagerank"],
                                    "imp_score" => $result["3y_cc"],
                                    "cc_score" => $result["citation_count"],
                                    "pop_class" => $result["pop_class"],
                                    "inf_class" => $result["inf_class"],
                                    "imp_class" => $result["imp_class"],
                                    "cc_class" => $result["cc_class"],
                                    "is_oa" => $result["is_oa"],
                                    "type" => $result["type"],
                                    "search_context" =>  $result["search_context"],
                                    "show" => [
                                        "concepts" => true,
                                        "annotations" => true,
                                        "relations" => true,
                                        "open_access" => true,
                                        "search_context" => true,
                                        "copy_link" => true,
                                        "bookmark" => true,
                                    ],
                                    "space_url_suffix" => $space_model->url_suffix,
                                    "space_annotation_db" => $space_model->annotation_db
                                ]);
                            } ?>
                        </div>
                    </div>
                    <div id="results_ftr" class='row'>
                        <div class='col-md-12 text-center'><?= LinkPager::widget(['pagination'=>$results['pagination'],
                            'maxButtonCount'=>5,
                            'firstPageLabel' => '<i class="fa-solid fa-backward-fast"></i>',
                            'lastPageLabel'  => '<i class="fa-solid fa-forward-fast"></i>']);
                        ?></div>
                    </div>
                </div>
                    <?php } else { ?>
                        <div id='results_set'>
                            <?php if( $keywords != "" ) { ?>
                                <p class="help-text" style="text-align: center;">No results found!<br/>
                                Please check your spelling or try again with different input parameters</p>
                            <?php } else { ?>

                                <!-- Layout Blocks Container -->
                                <div class="container">
                                    <div class="bip-home-layout bip-animate">

                                        <!-- BIP info Panel -->
                                        <div class="panel panel-default bip-animate bip-info-panel">
                                            <div class="panel-body">
                                            BIP! Services, is a suite of services designed to support researchers and other stakeholders with scientific knowledge discovery, research assessment, and other use cases related to their everyday routines.  
                                            <a href="<?= Url::to(['site/about']) ?>" class="main-green">
                                                Learn more <i class="fa fa-external-link-square" aria-hidden="true"></i>
                                            </a>
                                            </div>
                                        </div>

                                        <div class="bip-main-grid">

                                            <!-- Getting Started Panel -->
                                            <div class="panel panel-default bip-animate bip-started">
                                                <div class="panel-body">
                                                    <h3>Getting started</h3>
                                                    <ul>
                                                        
                                                        <li>
                                                            <a href="<?= Url::to(['/site/help', '#' => 'create-bip-services-account']) ?>" class="light-grey-link">
                                                                How do I create a BIP! Services account?
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="<?= Url::to(['/site/help', '#' => 'create-bip-scholar-academic-profile']) ?>" class="light-grey-link">
                                                                How do I create a BIP! Scholar academic profile?
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="<?= Url::to(['/site/indicators']) ?>" class="light-grey-link">
                                                                What indicators are used in BIP! Services?
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="<?= Url::to(['/site/data', '#' => 'downloads']) ?>" class="light-grey-link">
                                                                Can I download the data?
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="<?= Url::to(['/site/data', '#' => 'api']) ?>" class="light-grey-link">
                                                                Is there a publicly accessible API?
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="<?= Url::to(['/site/about', '#' => 'how-to-cite']) ?>" class="light-grey-link">
                                                                How do I cite BIP! Services?
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <!-- Right Column -->
                                            <div class="bip-side-boxes bip-animate">
                                                <!-- Research Works Counter -->
                                                <div class="panel panel-default bip-counter bip-animate">
                                                    <div class="panel-body text-center">
                                                        <strong class="animate-indicator" id="counter-number" data-target="<?= $articlesCount ?>">0</strong><br/>
                                                        Research works indexed
                                                    </div>
                                                </div>

                                                <!-- Carousel -->
                                                <div class="panel panel-default bip-animate">
                                                    <div class="panel-body">
                                                        <div class="bip-carousel">
                                                            <div class="bip-carousel-inner">
                                                                <div class="bip-carousel-item">
                                                                    <?= Html::img("@web/img/bip-minimal.png", ['class' => 'bip-slide-logo']) ?>
                                                                    <a href="<?= Url::to(['/scholar']) ?>" class="bip-slide-button">Scholar</a>
                                                                </div>
                                                                <div class="bip-carousel-item">
                                                                    <?= Html::img("@web/img/bip-minimal.png", ['class' => 'bip-slide-logo']) ?>
                                                                    <a href="<?= Url::to(['/spaces']) ?>" class="bip-slide-button">Spaces</a>
                                                                </div>
                                                                <div class="bip-carousel-item">
                                                                    <?= Html::img("@web/img/bip-minimal.png", ['class' => 'bip-slide-logo']) ?>
                                                                    <a href="<?= Url::to(['/readings']) ?>" class="bip-slide-button">Readings</a>
                                                                </div>
                                                            </div>

                                                            <div class="bip-dots">
                                                                <span class="dot active" data-slide="0"></span>
                                                                <span class="dot" data-slide="1"></span>
                                                                <span class="dot" data-slide="2"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
</div>
    <?php
        Modal::begin(['headerOptions' => ['id' => 'modalHeader'],
                      'header' => '<h4 class="grey-text">Search context (appearances of search keywords)</h4>',
                      'id' => 'modal',
                      'size' => 'modal-lg',
                       //keeps from closing modal with esc key or by clicking out of the modal.
                       // user must click cancel or X to close
                       //'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE]
                    ]);
        echo "<div id='modalContent' class='grey-text'></div>";
        Modal::end();

        echo CustomBootstrapModal::widget(['id' => 'versions-modal']);
        echo CustomBootstrapModal::widget(['id' => 'relations-modal']);

    ?>

