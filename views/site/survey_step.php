<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use yii\web\View;
use yii\bootstrap\Modal;
use app\components\CustomBootstrapRadioList;
use app\components\ProgressSteps;

use Yii;

$this->title = 'BIP! Finder';
$this->registerJsFile('@web/js/resultsFunctions.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);

// this is added with POS_END, as when added at the the top, the beforeSubmit form hook is ignored
$this->registerJsFile('@web/js/surveyFunctions.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile("@web/css/survey.css", ['depends' => [\yii\bootstrap\BootstrapAsset::className()]]);

// get previously checked rows from session variable. 
// used to highlight previously selected rows when going back
$previously_checked = [];
$previous_comment = '';

if(isset($_SESSION['results'][$step]) 
    && $_SESSION['results'][$step]['keywords'] == $keywords){

    $previous_page_data = $_SESSION['results'][$step];
    $previously_checked = explode(',', $previous_page_data['checked']);
    $previous_comment = $previous_page_data['comments'];
}

// no or incomplete results are set via a session param as 
// the flow is redirected to the first try of the step
$no_results = false;

if($session->has('no_results') && $session->get('no_results') == true){
    $no_results = true;
    $session->remove('no_results');
}
?>
<div class="container site-index">
    <div class="jumbotron">
        <div class="col-md-1" style="margin-top: 25px;">
             <?php if($step != 1){ ?>
                <span class="input-group-btn">
                    <?php 

                        $prev_step = $_SESSION['results'][$step-1];
                        $link = ($n == 2) ? Url::to(['site/survey', 'step' => $step - 1]) : Url::to(['site/survey', 'keywords' => $prev_step['keywords'], 'ordering' => $prev_step['ordering'], 'step' => $step - 1]);
                        echo Html::a('<i class="fa fa-angle-double-left" aria-hidden="true"></i> Back', $link, ['class' => 'btn btn-success form-control', 'title' => 'Go back to previous step']);
                    ?>
                    </span>
            <?php } ?>
        </div>
        <div class="col-md-offset-1 col-md-8">
            <?= ProgressSteps::widget([
                    'col_class' => 'col-md-2',
                    'active' => ($step - 1),
                    'steps' => [
                        [
                            'title' => 'Step 1', 
                            'message' => 'Enter keywords to retrieve articles'
                        ], 
                        [
                            'title' => 'Step 2', 
                            'message' => 'Select relevant articles from the list below'
                        ], 
                        [
                            'title' => 'Step 3', 
                            'message' => 'Select extra articles from the list below'
                        ], 
                        [
                            'title' => 'Step 4', 
                            'message' => 'Enter keywords to retrieve articles'
                        ], 
                        [
                            'title' => 'Step 5', 
                            'message' => 'Select relevant articles from the list below'
                        ], 
                        [
                            'title' => 'Step 6', 
                            'message' => 'Select extra articles from the list below'
                        ]
                    ]
                ]); 
            ?>
        </div>
        <div class="col-md-offset-1 col-md-1" style="margin-top:25px;">
            <span class="input-group-btn">
                <?= Html::button('Next <i class="fa fa-angle-double-right" aria-hidden="true"></i>', ['class' => 'btn btn-success form-control', 'onclick' => ($n == 1) ? '$("form#search-form").submit();' : '$("form#survey-form").submit();', 'title' => 'Proceed to next step']) ?>
            </span>
        </div>
            <?php
            $keywords_params = ['autofocus' => true, 'placeholder'=>'Enter keywords to retrieve articles...', 'class'=>'search-box form-control'];
            if($n > 1)
                $keywords_params['disabled'] = true;

            if( $keywords!='' )
                $keywords_params['value'] = $keywords;
            $form = ActiveForm::begin(['id' => 'search-form', 'method'=>'get', 'action'=> Url::to(['site/survey', 'step' => $step + 1]), 'options'=>['onsubmit'=>'showLoading();']]);
            ?>            
            <div class='row'>
                <div class="col-md-8 col-md-offset-2">
                    <div class='input-group keywords-input'>
                        <?= $form->field($model, 'keywords')->textInput($keywords_params) ?>
                    </div>
                </div>
            </div>
            <div class='row'>
                <?php if($n == 1){ ?>
                <div class="col-md-8 col-md-offset-2">
                    <div class="form-group field-ordering">
                        <label class="control-label" for="category">What papers are you looking for?</label>
                        <input type="hidden" name="category" value=""><div id="category" popularity="{&quot;Selected&quot;:true}" style="display: inline-block;"><div class="radio radio-success radio-inline">
                        <input id="popularity" name="category" checked="checked" value="popularity" type="radio"><label for="popularity">Popular</label></div>
                        <div class="radio radio-success radio-inline">
                        <input id="influence" name="category" value="influence" type="radio"><label for="influence">Well-established</label></div></div>
                    </div>
                </div>
                <?php } ?>
            
            </div>
            <?php ActiveForm::end(); ?>     

            <div class='row'>
    		<div id="loading_results">
                <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <br/><br/>
                Loading results (it may take a couple of seconds)...
    		</div>
            </div>

            <?php 
            if(!empty($results['rows']) && !$no_results)
            {
            ?>
            <div class='container-fluid'>
                <div id='results_tbl' class='row'>             
                    <div class='col-md-12'>
						<table id="results_set" class="table table-hover">
                            <thead>
                                <tr>
									<th></th>
                                    <th>Title</th>
                                    <th></th>
                                    <th>Venue</th>
                                    <th>Year</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach($results['rows'] as $row )
                            {
                            ?>
                                <tr id="res_<?= $row['internal_id'] ?>" class="text-left selected-after-click">
									<td id="checkbox_<?= $row['internal_id'] ?>">
                                    <!-- check paper if it was selected in previous step -->
                                    <?php if (in_array($row['internal_id'], $already_checked)){ ?>      
       								     <input checked disabled="true" type="checkbox" name="selected_papers" value="<?= $row['internal_id'] ?>" />
                                    <?php } else if (in_array($row['internal_id'], $previously_checked)){ ?>
                                         <input checked type="checkbox" name="selected_papers" value="<?= $row['internal_id'] ?>" onclick='handleCheckboxClick(this);' />
                                    <?php } else { ?>
                                        <input type="checkbox" name="selected_papers" value="<?= $row['internal_id'] ?>" onclick='handleCheckboxClick(this);' />
                                    <?php } ?>
									</td>
                                    <td id="res_<?= $row['internal_id'] ?>_t"><?= Yii::$app->bipstring->lowerize(Yii::$app->bipstring->shortenString($row['title'],90)) ?></td>
                                    <td>    
                                        <a class="context-popup-link" data-target="#context-modal" data-toggle="modal" href="#modal" title='Show keyword context'><span class='context'>context </span><i class="fa fa-eye" aria-hidden="true"></i></a>
                                    </td>                                                
                                    <td id="res_<?= $row['internal_id'] ?>_j">
				                       <?= $row['journal'] ?>
                                    </td>
                                    <td id="res_<?= $row['internal_id'] ?>_y">
                                        <?= $row['year'] ?>
                                    </td>
                                    <td>
                                        <?= Html::a('<i class="fa fa-info-circle" aria-hidden="true"></i>', ['site/redirect'], ['class' => 'my-btn', 'title' => 'Show details', 'target' => '_blank', 'data' => ['method' => 'post', 'params' => ['pmc'=> $row['pmc'], 'action' => 'details', 'keywords' => $keywords, 'paper_id' => $row['internal_id'], 'source' => 'survey', 'session_id' => $session->getId()]]]); ?>
                                        <?= Html::a('<i class="fa fa-external-link-square" aria-hidden="true"></i>', ['site/redirect'], ['class' => 'my-btn', 'title' => 'Show article in PMC', 'target' => '_blank', 'data' => ['method' => 'post', 'params' => ['pmc'=> $row['pmc'], 'action' => 'pubmed', 'keywords' => $keywords, 'paper_id' => $row['internal_id'], 'source' => 'survey', 'session_id' => $session->getId()]]]); ?>
                                    </td>
                                </tr>
                                <tr id="res_<?= $row['internal_id'] ?>_context" class="kwd-context title">
                                    <td colspan="5">
                                        <?php if (!empty($row['author_contexts']))
                                        {
                                        ?>
                                            <span class="context-list-title">Author:</span>
                                            <ul>
                                            <?php foreach($row['author_contexts'] as $context)
                                            {
                                                echo "<li>" . $context . "</li>";
                                            }
                                            ?>
                                            </ul>
                                        <?php
                                        }
                                        if(!empty($row['title_contexts']))
                                        {
                                        ?>
                                            <span class="context-list-title">Title:</span>
                                            <ul>
                                            <?php foreach($row['title_contexts'] as $context)
                                            {
                                                echo "<li>" . $context . "</li>";
                                            }
                                            ?>
                                            </ul>
                                        <?php
                                        }
                                        if (!empty($row['abstract_contexts']))
                                        {
                                        ?>
                                            <span class="context-list-title">Abstract:</span>
                                            <ul>
                                            <?php foreach($row['abstract_contexts'] as $context)
                                            {
                                                echo "<li>" . $context . "</li>";
                                            }
                                            ?>
                                            </ul>    
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>                            
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                        <?php
                            if(!empty($results['rows'])){
                                $survey_form = '';
                                if($n == 3){
                                     $survey_form = ActiveForm::begin([
                                                        'id' => 'survey-form', 
                                                        'method' => 'post', 
                                                        'action' => Url::to([
                                                            'site/survey', 
                                                            'step' => $step + 1, 
                                                        ])
                                                    ]); 
                                } else {
                                    $survey_form = ActiveForm::begin([
                                                        'id' => 'survey-form', 
                                                        'method' => 'post', 
                                                        'action' => Url::to([
                                                            'site/survey', 
                                                            'keywords' => $keywords, 
                                                            'step' => $step + 1,
                                                        ]), 
                                                        'options' => [
                                                            'onsubmit' => 'showLoading();disableKeywordsInput();'
                                                        ]
                                                    ]); 
                                }

                                $comments_params = ['placeholder' => 'Enter your comments here', 'class'=>'search-box form-control', 'rows' => 4];

                                // set comments input if it was previously filled, used with back button
                                if($previous_comment != ''){
                                    $survey_model->comments = $previous_comment;
                                }

                                echo $survey_form->field($survey_model, 'comments', [
                                    'template' => '<div style="float:left;">{label}</div>{input}{error}{hint}'
                                ])->textarea($comments_params);

                                // this hidden input will be filled with JS on beforeSubmit form event, see file surveyFunction.js
                                echo $survey_form->field($survey_model, 'checked')->hiddenInput(['value'=> '', 'id' => 'surveyform-checked'])->label(false);
                                echo $survey_form->field($survey_model, 'session_id')->hiddenInput(['value'=> $session->getId()])->label(false);
                                echo $survey_form->field($survey_model, 'keywords')->hiddenInput(['value'=> $keywords])->label(false);
                                echo $survey_form->field($survey_model, 'ordering')->hiddenInput(['value'=> $ordering])->label(false);
                                echo $survey_form->field($survey_model, 'start_time')->hiddenInput(['value'=> $start_time])->label(false);
                                echo $survey_form->field($survey_model, 'step')->hiddenInput(['value'=> $step])->label(false);
                                echo $survey_form->field($survey_model, 'category')->hiddenInput(['value'=> $category])->label(false);

                                // get only the internal paper ids of the results
                                $paper_ids_in_results = implode(array_column($results["rows"], 'internal_id'), ',');
                                echo $survey_form->field($survey_model, 'papers')->hiddenInput(['value'=> $paper_ids_in_results])->label(false);
                                ActiveForm::end();
                            }
                        ?>
		          </div>
                </div>
            </div>
            <?php } else { ?>
                <div id='results_set'>
                    <?php if( $no_results ) { ?>   
                        <span id="no_results_msg"> 
                            Not enough results were found for the specified input, please try again with different terms.
                        </span>
                    <?php } ?>
    			</div> 
            <?php } ?>
    </div>
	

    <div id="top_paper_graph_div">
        
    </div>
    <?php
        Modal::begin(['headerOptions' => ['id' => 'modalHeader'],
                      'header' => '<h4>Search context of: \'' . implode("', '", preg_split("/\s+/", $keywords)) . '\'</h4>',
                      'id' => 'modal',
                      'size' => 'modal-lg',
                       //keeps from closing modal with esc key or by clicking out of the modal.
                       // user must click cancel or X to close
                       //'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE]
                    ]);
        echo "<div id='modalContent'></div>";
        Modal::end();
    ?>   
<!-- normally a div should close here. In order however to have the footer stick to the bottom, we have to put it
at the end of the overwrap container, which is ifound in the layout. Thus we don't close what is done here -->
