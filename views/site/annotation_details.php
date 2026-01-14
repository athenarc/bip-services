<?php

use app\assets\TinyColorAsset;
use app\components\ResultItem;
use app\components\SummaryPanel;
use app\models\SummaryUsage;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = 'BIP! Services - Finder';

/* @var $this yii\web\View */
$this->registerJsFile('@web/js/resultsFunctions.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/summarize.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/chartjs_bar_plot.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/chartjs/chart_v4.2.0.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/annotationEvolution.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/tags.css');

// Register tinycolor.js as an asset bundle
TinyColorAsset::register($this);

$in_space = ($space_model->url_suffix !== null && $space_model->url_suffix !== '');

// Register space colors early if in space (right after tinycolor.js)
if ($in_space) {
    $spaceColor = $space_model->theme_color;
    // set_space_colors.js depends on TinyColorAsset, ensuring it loads after tinycolor.js
    $this->registerJsFile('@web/js/set_space_colors.js', ['position' => View::POS_HEAD, 'depends' => [TinyColorAsset::className()]]);
    // Call setSpaceColors function after both scripts are loaded
    $this->registerJs("if (typeof setSpaceColors !== 'undefined') { setSpaceColors('{$spaceColor}'); }", View::POS_HEAD);
}
?>

<div class='row'>
    <div class='col-xs-12'>
        <div class='article-header'>
            <?php if ($has_metadata_query && ! empty($annotation_info)): ?>
                <?= Html::encode($space_annotation->name ?? $space_annotation->display_name_plural ?? 'Annotation') ?>: <?= Html::encode($annotation_info['label'] ?? '') ?>
            <?php else: ?>
                <?= Html::encode($space_annotation->name ?? $space_annotation->display_name_plural ?? 'Annotation') ?> with id: <?= Html::encode($annotation_id) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class='row'>
    <div class='col-xs-12'>
        <?php if ($has_metadata_query && ! empty($annotation_info)): ?>
            <?php foreach ($annotation_info['data'] as $annotation_data): ?>
                <div class='article-info' style = 'color:unset;'>
                    <b><?= ucfirst($annotation_data['label']) ?>:</b> 
                    <?php
                    $value = $annotation_data['value'] ?? null;

                    if (empty($value)) {
                        echo 'N/A';
                    } elseif (is_array($value)) {
                        // Handle array values: join with comma and space
                        $formattedValue = implode(', ', array_map(function ($item) {
                            return ucfirst(str_replace('"', "'", (string) $item));
                        }, $value));
                        echo $formattedValue;
                    } else {
                        echo ucfirst(str_replace('"', "'", (string) $value));
                    }
                    ?>
                </div>
            <?php endforeach; ?>
            <div class='article-info' style = 'color:unset;'>
                <b>Source:</b> <?= str_replace('"', "'", Yii::$app->params['annotation_dbs'][$space_model->annotation_db]['name'] . ' knowledge graph') ?>
            </div>
        <?php else: ?>
            <div class='article-info' style='color:unset;'>
                <span class='text-warning'><i class='fa fa-exclamation-triangle'></i> No further information is available for this annotation.</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class='row'>
    <div class='col-xs-12'>
        <?php if (! empty($works)) : ?>
            <div id="annotation-evolution-data" 
                 data-space-url-suffix="<?= Html::encode($space_url_suffix) ?>"
                 data-annotation-id="<?= Html::encode($annotation_type_id) ?>"
                 data-id="<?= Html::encode($annotation_id) ?>"
                 style="display: none;"></div>
            <div class='row'>
                <div class='col-md-12'>
                    <h4 class="grey-text">Annotation Evolution</h4>
                </div>
            </div>
            <div id="annotation-charts-loading" class='row' style="min-height: 300px;">
                <div class='col-md-12 text-center' style="padding-top: 100px;">
                    <i class="fa fa-spinner fa-spin fa-3x grey-text"></i>
                    <p class="grey-text" style="margin-top: 20px;">Loading visualization data...</p>
                </div>
            </div>
            <div id="annotation-charts-container" class='row' style="display: none;">
                <div class='col-md-6'>
                    <div style="position:relative; height:100%; width:100%">
                        <canvas id="annotation-evolution-bar-plot"></canvas>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div style="position:relative; height:100%; width:100%">
                        <canvas id="annotation-citations-per-year-bar-plot"></canvas>
                    </div>
                </div>
            </div>
            <br/>
            <div class='row grey-text'>
                <div class='col-xs-12 text-center'>
                    <div class = "inline-block-d" style = "margin:0 8px">
                        <?php
                        $form = ActiveForm::begin([
                            'method' => 'get',
                            'action' => Url::to([
                                'site/annotation',
                                'space_url_suffix' => $space_model->url_suffix,
                                'annotation_id' => $space_annotation->id,
                                'id' => $annotation_id
                            ]),
                            'options' => ['class' => 'inline-form']
                        ]);
                        ?>
                        <div class="form-group field-ordering" style="display: inline-block; margin: 0;">
                            <b>Ordering:</b> <?= Html::dropDownList('ordering', $ordering ?? 'popularity', [
                                'popularity' => 'Popularity',
                                'influence' => 'Influence',
                                'citation_count' => 'Citation Count',
                                'impulse' => 'Impulse',
                                'year' => 'Year'
                            ], [
                                'onchange' => '$(this).closest(\'form\').submit()',
                                'style' => ['display' => 'inline-block', 'width' => 'auto', 'color' => 'grey', 'margin-left' => '5px'],
                                'class' => 'form-control'
                            ]); ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
            <div id="results_hdr" class='row'>
                <div class='col-sm-12 col-md-3 text-center results-header' style="margin-bottom: 15px;">
                    <?= Yii::$app->formatter->asDecimal($pagination->totalCount, 0) ?> results (<?=  Yii::$app->formatter->asDecimal($pagination->pageCount, 0) ?> pages)
                </div>
                <div class='col-sm-12 col-md-6 text-center' style="margin-bottom: 15px;">
                    <?= LinkPager::widget([
                        'pagination' => $pagination,
                        'maxButtonCount' => 5,
                        'firstPageLabel' => '<i class="fa-solid fa-backward-fast"></i>',
                        'lastPageLabel' => '<i class="fa-solid fa-forward-fast"></i>'
                    ]);
                    ?>
                </div>
                <?php
                    $threshold = \app\models\AdminOptions::getValue('summarize_button_threshold') ?? 20;
                ?>
                <div class='col-sm-12 col-md-3 text-center' style="margin-bottom: 15px;">
                    <?php if (SummaryUsage::isAiAssistantEnabledForCurrentUser()): ?>
                        <button id="summarizeBtn" class="btn btn-default btn-sm" 
                                data-paper-ids='<?= json_encode(array_map(function ($result) {
                    return $result['internal_id'];
                }, $works)) ?>'
                                data-keywords=''
                                data-threshold="<?= $threshold ?>"
                            >
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Summarize top results
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?= SummaryPanel::widget() ?>
            <div id='results_tbl'>
                <?php foreach ($works as $result) {
                    echo ResultItem::widget([
                        'impact_indicators' => $impact_indicators,
                        'internal_id' => $result['internal_id'],
                        'doi' => $result['doi'],
                        'dois_num' => $result['dois_num'],
                        'title' => $result['title'],
                        'authors' => $result['authors'],
                        'journal' => $result['journal'],
                        'year' => $result['year'],
                        'concepts' => $result['concepts'],
                        // "annotations" => $result["annotations"] ?? null,
                        'user_id' => $result['user_id'],
                        'pop_score' => $result['attrank'],
                        'inf_score' => $result['pagerank'],
                        'imp_score' => $result['3y_cc'],
                        'cc_score' => $result['citation_count'],
                        'pop_class' => $result['pop_class'],
                        'inf_class' => $result['inf_class'],
                        'imp_class' => $result['imp_class'],
                        'cc_class' => $result['cc_class'],
                        'is_oa' => $result['is_oa'],
                        'type' => $result['type'],
                        'show' => [
                            'concepts' => true,
                            // "annotations" => true,
                            'open_access' => true,
                            'copy_link' => true,
                            'bookmark' => true,
                        ],
                        'space_url_suffix' => $space_model->url_suffix,
                        'space_annotation_db' => $space_model->annotation_db
                    ]);
                } ?>
            </div>

                <?php endif; ?>
        </div>
</div>
