<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use yii\web\View;

use app\components\ResearcherResultItem;

use Yii;

/* @var $this yii\web\View */
$this->title = 'BIP! Scholar - Search profiles';

?>

<div class="site-index">
    <div class="jumbotron">
        <h1>
            <?= Html::img("@web/img/bip-minimal.png", ['class' => 'img-responsive center-block', /*'width' => 100, 'height' => 89*/]) ?>
        </h1>
        <p style = "margin-top:-10px;">
            Explore public scholar profiles
        </p>
        

        <?php
            $form = ActiveForm::begin(['id' => 'search-form', 'method' => 'GET', 'action' => Url::to(['scholar/search']), 'options'=>[]]);
        ?>

        <div class='row'>
            <div class="col-md-8 col-md-offset-2">
                <div class='has-search'>
                    <?= $form->field($search_model, 'keywords', ['template' => "{input}<span class='glyphicon glyphicon-search form-control-feedback'></span>{error}"])
                        ->input('search', [
                            'autofocus' => true, 
                            'aria-label' => 'Search', 
                            'placeholder' => 'Search scholar profiles using name or ORCiD', 
                            'class'=>'search-box form-control']) ?>
                    
                    <!-- class sr-only instead of hidden because of Safari browser -->
                    <input type="submit" class="sr-only" hidefocus="true" tabindex="-1">
                </div>
            </div>
        </div>
        <div class='row'>
            <div id="loading_results">
                <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <br/><br/>
                Loading results (it may take a couple of seconds)...
            </div>
        </div>
        <div class='row grey-text'>
            <div class='col-xs-12'>
                <div class = "inline-block-d" style = "margin:0 8px">
                    <?= $form->field($search_model, 'ordering')->dropdownList([
                            'name' => 'Name (alphabetically)',
                        ], 
                        [
                            'onchange' => '$(this).closest(\'form\').submit()',
                            'style' => [ 'display' => 'inline-block', 'width' => 'auto', 'color' => 'grey' ]
                        ]
                    );?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

        <?php if(!empty($results['rows'])) { ?>
            <div class='container-fluid'>
                <div id="results_hdr" class='row'>
                    <div class='col-md-3 text-center results-header'><?= Yii::$app->formatter->asDecimal($results['pagination']->totalCount, 0) ?> results (<?=  Yii::$app->formatter->asDecimal($results['pagination']->pageCount,0) ?> pages)</div>
                    <div class='col-md-6 text-center'><?= LinkPager::widget(['pagination'=>$results['pagination'],
                        'maxButtonCount'=>5,
                        'firstPageLabel' => '<i class="fa-solid fa-backward-fast"></i>',
                        'lastPageLabel'  => '<i class="fa-solid fa-forward-fast"></i>']);
                    ?>
                    </div>
                </div>
                <div id='results_tbl' class='row'>
                    <div class='col-md-12'>
                        <?php foreach($results['rows'] as $result ) {
                            echo ResearcherResultItem::widget([
                                "id" => $result->id,
                                "orcid" => $result->orcid,
                                "name" => $result->name
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
                <?php if( $search_model->keywords != "" ) { ?>
                    <p class="help-text" style="text-align: center;">No results found!<br/>
                    Please check your spelling or try again with different input parameters</p>
                <?php } else { ?>
                    <br/><br/><br/>
                    <p>More details about BIP! Scholar can be found in our publication:</p>
                    <div class="panel panel-default text-left">
                        <div class="panel-body">
                            T. Vergoulis, S. Chatzopoulos, K. Vichos, I. Kanellos, A. Mannocci, N. Manola, P. Manghi: <b>BIP! Scholar: A Service to Facilitate Fair Researcher Assessment.</b> <i>Joint Conference on Digital Libraries (JCDL)</i>, 2022
                        </div>
                    </div>
                    <p><small>We kindly ask that any published research that makes use of BIP! Scholar or its data cites the paper above.</small></p>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>

