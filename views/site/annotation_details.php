<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use yii\web\View;
use yii\bootstrap\Modal;
use app\components\ResultItem;
use yii\helpers\ArrayHelper;
use app\models\Indicators;

use Yii;

$this->title = 'BIP! Services - Finder';

/* @var $this yii\web\View */
$this->registerJsFile('@web/js/resultsFunctions.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/tinycolor.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/tags.css');


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
            <?= $annotation_info['label'] ?>
        </div>
    </div>
</div>

<div class='row'>
    <div class='col-xs-12'>
        <?php foreach($annotation_info['data'] as $annotation_data): ?>
            <div class='article-info' style = 'color:unset;'>
                <b><?= ucfirst($annotation_data['label']) ?>:</b> <?= empty(($annotation_data['value'])) ? 'N/A' : ucfirst(str_replace("\"", "'", $annotation_data['value']))?>
            </div>
        <?php endforeach; ?>
            <div class='article-info' style = 'color:unset;'>
                <b>Source:</b> <?= str_replace("\"", "'",  Yii::$app->params['annotation_dbs'][$space_model->annotation_db]['name'] . ' knowledge graph') ?>
            </div>
    </div>
</div>

<div class='row'>
    <div class='col-xs-12'>
        <?php if(!empty($works)) : ?>

            <div id="results_hdr" class="row">
                <div class='col-md-3 text-center results-header'><?= Yii::$app->formatter->asDecimal($pagination->totalCount, 0) ?> results (<?=  Yii::$app->formatter->asDecimal($pagination->pageCount,0) ?> pages)</div>
                <div class='col-md-6 text-center'><?= LinkPager::widget(['pagination'=>$pagination,
                    'maxButtonCount'=>5,
                    'firstPageLabel' => '<i class="fa-solid fa-backward-fast"></i>',
                    'lastPageLabel'  => '<i class="fa-solid fa-forward-fast"></i>']);
                ?>
                </div>
            </div>
            <div id='results_tbl'>
                <?php foreach($works as $result ) {
                    echo ResultItem::widget([
                        "impact_indicators" => $impact_indicators,
                        "internal_id" => $result["internal_id"],
                        "doi" => $result["doi"],
                        "dois_num" => $result["dois_num"],
                        "title" => $result["title"],
                        "authors" => $result["authors"],
                        "journal" => $result["journal"],
                        "year" => $result["year"],
                        "concepts" => $result["concepts"],
                        // "annotations" => $result["annotations"] ?? null,
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
                        "show" => [
                            "concepts" => true,
                            // "annotations" => true,
                            "open_access" => true,
                            "copy_link" => true,
                            "bookmark" => true,
                        ],
                        "space_url_suffix" => $space_model->url_suffix,
                        "space_annotation_db" => $space_model->annotation_db
                    ]);
                } ?>
            </div>

                <?php endif; ?>
        </div>
</div>
