<?php

use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use app\components\CustomBootstrapRadioList;
use app\components\CustomBootstrapCheckboxList;
use yii\jui\AutoComplete;
use wbraganca\dynamicform\DynamicFormWidget;


$this->registerJsFile('@web/js/spacesAdmin.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

?>


<div class="spaces-form">

    <?php if ($model->isNewRecord) : ?>
        <p>Please fill out the following fields to create a new space:</p>
    <?php else : ?>
        <p>Please fill out the following fields to update an existing space:</p>
    <?php endif ?>

    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'action' => ['site/admin-spaces'],
        ]);?>


    <div class="form-group">
        <div class="flex-wrap items-center">

            <div  style = "margin-right:20px">
            <?= Html::dropDownList('space_id_update', $model->id, $spacesArray, [
                'prompt' => 'New space', 'id' => 'space-dropdown', 'class' => 'form-control', 'onchange' => 'this.form.submit();'
                ]) ?>
            </div>

            <?php if (!$model->isNewRecord) : ?>
            <div style="margin-top: 10px;">
            <?= Html::a("Link to " . $model->url_suffix . " <i class='fa fa-external-link-square main-green' aria-hidden='true'></i>",
                    Url::to(['/search' . '/' .  $model->url_suffix]),
                    ['class' => 'main-green', 'target' => '_blank']);
            ?>
            </div>
            <?php endif ?>

        </div>
    </div>


    <?php ActiveForm::end(); ?>


    <?php $form = ActiveForm::begin([
        'id' => 'space-form',
        'method' => 'post',
        'action' => ['site/admin-save-spaces'],
        'options' => ['enctype' => 'multipart/form-data'],
        ]);?>


    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <h3>General info</h3>

    <?= $form->field($model, 'url_suffix')->textInput(['maxlength' => true, 'class' => 'search-box form-control']) ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => true, 'class' => 'search-box form-control']) ?>

    <?= $form->field($model, 'logo_upload')->fileInput(['disabled' => isset($model->logo) ? false : true]) ?>


    <?= $form->field($model, 'logo_default', [
        'template' => "{input}\n{hint}\n{error}",
    ])->label(false)->dropDownList([
        '1' => 'Use the default logo',
        '0' => 'Upload new logo'
    ], [
        'enableClientValidation' => false,
    ]) ?>

    <?= $form->field($model, 'theme_color')->textInput(['maxlength' => true, 'class' => 'search-box form-control', 'type'=>'color', 'style' => 'width: 70px;']) ?>

    <div id = "spaces-form_img">
        <?php if(isset($model->logo)): ?>
            <?= Html::img($model->uploadLogoPath() . $model->logo, ['class' => '', 'style' => 'max-height: 150px; max-width: 50%;']) ?>
        <?php endif; ?>
    </div>


    <h3>Search options presets</h3>

    <?= $form->field($model, 'ordering')->dropdownList([
                            'popularity' => 'Popularity',
                            'influence' => 'Influence',
                            'citation_count' => 'Citation Count',
                            'impulse' => 'Impulse',
                            'year' => 'Year'
                            ]);?>

    <?= $form->field($model, 'relevance')->dropdownList([
                            'high' => 'Yes',
                            'low' => 'No'
                            ]);?>

    <h3>Filtering presets</h3>

    <?=  CustomBootstrapCheckboxList::widget([
            'name' => 'type', 'model' => $model, 'form' => $form,
            'items' => array_map(function ($value) {return $value['name'];}, Yii::$app->params['work_types']),
            'item_class' => 'checkbox checkbox-custom checkbox-inline',
            'unselect' => ''
        ]);
    ?>


    <?= $form->field($model, 'start_year')->textInput(['type' => 'number', 'class' => 'search-box form-control']) ?>
    <?= $form->field($model, 'end_year')->textInput(['type' => 'number','class' => 'search-box form-control']) ?>

    <?php

    $score_percentages = [
        'all' => 'All',
        'top001' => 'Top 0.01%',
        'top01' => 'Top 0.1%',
        'top1' => 'Top 1%',
        'top10' => 'Top 10%'];

    ?>

    <?= CustomBootstrapRadioList::widget([
        'name' => 'popularity',
        'model' => $model,
        'form' => $form,
        'items' => $score_percentages,
        'unselect' => null]); ?>

    <?= CustomBootstrapRadioList::widget([
        'name' => 'influence',
        'model' => $model,
        'form' => $form,
        'items' => $score_percentages,
        'unselect' => null]); ?>

    <?= CustomBootstrapRadioList::widget([
        'name' => 'cc',
        'model' => $model,
        'form' => $form,
        'items' => $score_percentages,
        'unselect' => null]); ?>

    <?= CustomBootstrapRadioList::widget([
        'name' => 'impulse',
        'model' => $model,
        'form' => $form,
        'items' => $score_percentages,
        'unselect' => null]); ?>


    <?= $form->field($model, 'topics')->widget(AutoComplete::class, [

        'name' => "topics",
        'clientOptions' =>
        [
            'source' =>  new JsExpression('
                function(request, response) {
                    // extract last value
                    var term = request.term.split(/,\s*/).pop();
                    var autoCompleteUrl = "' . Url::toRoute(['site/auto-complete-concepts/both/10']) . '";
                    $.getJSON(autoCompleteUrl, {
                        term: term
                    }, response);
                }
            ')
        ],
        'clientEvents' =>
        [
            'search' => new JsExpression('
                function(event, ui) {
                    // extract last value
                    var term = this.value.split(/,\s*/).pop();
                    if (term.length < 1) {
                        return false;
                    }
                }
            '),
            'focus' => new JsExpression('
                function(event, ui) {
                    return false;
                }
            '),
            'select' => new JsExpression('
                function(event, ui) {
                    // Get the value selected from the dropdown
                    if(ui.item.value == "No suggestions found") return false;
                    var terms = this.value.split( /,\s*/ );
                    terms.pop();
                    terms.push(ui.item.value);
                    terms.push("");
                    this.value = terms.join(", ");
                    return false;
                }
            '),
            'close' => new JsExpression('
                function(event, ui) {
                    // Get the value selected from the dropdown
                    var terms = this.value.split( /,\s*/ );
                    terms.pop();
                    terms.push("");
                    this.value = terms.join(", ");
                    return false;
                }
            ')
        ],
        //html options
        'options' => ['class'=>'form-control search-box', 'placeholder' => "Select Topics"],
    ]) ?>

    <h3>Annotations</h3>

    <?php

        $annotation_db_options = array_map(function($db) {
            return $db['name'];
        }, Yii::$app->params['annotation_dbs']);

        echo $form->field($model, 'annotation_db')->dropdownList($annotation_db_options, ['prompt' => '-- Select a database for annotations --']);

        echo $form->field($model, 'graph_db_system')->dropdownList(Yii::$app->params['graph_db_systems'], ['prompt' => '-- Select a database system --']);

    ?>

    <?php 
        DynamicFormWidget::begin([
            'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
            'widgetBody' => '.container-items', // required: css class selector
            'widgetItem' => '.item', // required: css class
            'limit' => 4, // the maximum times, an element can be cloned (default 999)
            'min' => 0, // 0 or 1 (default 1)
            'insertButton' => '.add-item', // css class
            'deleteButton' => '.remove-item', // css class
            'model' => $modelsSpacesAnnotations[0],
            'formId' => 'space-form',
            'formFields' => [
                'name',
                'description',
                'color',
                'query',
            ],
        ]); 
    ?>
    <div style = "margin-bottom:10px">
        <label class="pull-left" style="font-size: inherit;" >Annotation Data</label>
        <div class="pull-right">
            <button type="button" class="add-item btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="container-items"><!-- widgetContainer -->
    <?php foreach ($modelsSpacesAnnotations as $i => $modelSpacesAnnotations): ?>
        <div class="item panel panel-default"><!-- widgetBody -->
            <div class="panel-heading panel-heading-unset">
                <!-- <h3 class="panel-title pull-left">Annotation</h3> -->
                <!-- <label class="panel-title pull-left" style="font-size: inherit;" >Annotation Data</label> -->
                <div class="pull-right">
                    <button type="button" class="remove-item btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="panel-body">
                <?php
                    // necessary for update action.
                    if (! $modelSpacesAnnotations->isNewRecord) {
                        echo Html::activeHiddenInput($modelSpacesAnnotations, "[{$i}]id");
                    }
                ?>
                <div class="row parent-color-annotation">
                    <div class="col-sm-6">
                        <?= $form->field($modelSpacesAnnotations, "[{$i}]name")->textInput(['maxlength' => true, 'class' => 'search-box form-control']) ?>
                    </div>
                    <div class="col-sm-4">
                        <?= $form->field($modelSpacesAnnotations, "[{$i}]color")->textInput(['maxlength' => true, 'class' => 'search-box form-control color-annotation']) ?>
                    </div>
                    <div class="col-sm-2">
                        <label class="control-label">Color picker</label>
                        <input type="color" class="search-box form-control" value="<?= $modelSpacesAnnotations->color ?>" style="width: 70px;" oninput="$(this).closest('.parent-color-annotation').find('.color-annotation').val($(this).val());">
                        
                        <div class="help-block"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($modelSpacesAnnotations, "[{$i}]description")->textInput(['maxlength' => true, 'class' => 'search-box form-control']) ?>
                    </div>
                </div><!-- .row -->
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($modelSpacesAnnotations, "[{$i}]query")->textArea(['maxlength' => true, 'class' => 'search-box form-control', 'style' => 'resize: vertical;']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($modelSpacesAnnotations, "[{$i}]reverse_query")->textArea(['maxlength' => true, 'class' => 'search-box form-control', 'style' => 'resize: vertical;']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($modelSpacesAnnotations, "[{$i}]reverse_query_count")->textArea(['maxlength' => true, 'class' => 'search-box form-control', 'style' => 'resize: vertical;']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($modelSpacesAnnotations, "[{$i}]reverse_query_info")->textArea(['maxlength' => true, 'class' => 'search-box form-control', 'style' => 'resize: vertical;']) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php DynamicFormWidget::end(); ?>



    <div class="form-group">
        <?php if($model->isNewRecord): ?>
            <?= Html::submitButton('Create', ['class' => 'btn btn-custom-color']) ?>
        <?php else: ?>
            <?= Html::submitButton('Update', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Delete', ['site/admin-delete-spaces', 'space_id' => $model->id], ['class' => 'btn btn-danger']) ?>
        <?php endif ?>


    </div>

    <?php ActiveForm::end(); ?>

</div>