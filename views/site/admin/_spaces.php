<?php

use app\components\CustomBootstrapCheckboxList;
use app\components\CustomBootstrapRadioList;
use app\components\PubmedTypesModal;
use app\models\SpacesAnnotations;
use wbraganca\dynamicform\DynamicFormWidget;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

$this->registerJsFile('@web/js/spacesAdmin.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/on-off-my-switch.css');

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

            <?php if (! $model->isNewRecord) : ?>
            <div style="margin-top: 10px;">
            <?= Html::a(
                    'Link to ' . $model->url_suffix . " <i class='fa fa-external-link-square main-green' aria-hidden='true'></i>",
                    Url::to(['/search' . '/' . $model->url_suffix]),
                    ['class' => 'main-green', 'target' => '_blank']
                );
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

    <?= $form->field($model, 'theme_color')->textInput(['maxlength' => true, 'class' => 'search-box form-control', 'type' => 'color', 'style' => 'width: 70px;']) ?>

    <div id = "spaces-form_img">
        <?php if (isset($model->logo)): ?>
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

    <?=  CustomBootstrapCheckboxList::widget([
            'name' => 'is_oa', 'model' => $model, 'form' => $form,
            'items' => array_map(function ($v) { return $v['name']; }, array_filter(Yii::$app->params['openness'], function ($k) { return $k !== ''; }, ARRAY_FILTER_USE_KEY)),
            'item_class' => 'checkbox checkbox-custom checkbox-inline',
            'unselect' => ''
        ]);
    ?>


    <label class="control-label">NLM Types</label>
    <?= $form->field($model, 'has_pubmed_types', [
        'enableClientValidation' => false,
        'template' => "<div class=\"checkbox checkbox-custom checkbox-inline\">{input}\n{label}</div>\n{error}\n{hint}"
        ])->checkbox(
            [],
            false // IMPORTANT: render input and label separately so template {input}{label} works
        )
    ?>


    <div id="spaces-pubmed-types-container" style="<?= (empty($model->has_pubmed_types)) ? 'display:none;' : ''?>">

        <?= $form->field($model, 'pubmed_types')
            ->hiddenInput([
                'id' => 'spaces-pubmed-types-hidden',
                'value' => implode(',', (array) $model->pubmed_types)
            ])
            ->label(false)
        ?>


        <button type="button" class="btn btn-custom-color" data-toggle="modal" data-target="#spacesPubmedTypesModal">
            Edit NLM Types (<span id="spaces-pubmed-types-count"><?= count((array) $model->pubmed_types)?></span>)
        </button>
        <div class="help-block"></div>
    </div>

    <?= $form->field($model, 'start_year')->textInput(['type' => 'number', 'class' => 'search-box form-control']) ?>
    <?= $form->field($model, 'end_year')->textInput(['type' => 'number', 'class' => 'search-box form-control']) ?>

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
        'name' => 'topics',
        'clientOptions' => [
            'source' => new JsExpression('
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
        'clientEvents' => [
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
        'options' => ['class' => 'form-control search-box', 'placeholder' => 'Select Topics'],
    ]) ?>

    <h3>Annotations</h3>

        <div><label class="control-label">Anotations Filter</label></div>

        <?= $form->field($model, 'has_annotations_flag', [
            'enableClientValidation' => false,
            'options' => ['tag' => false], // prevent extra wrapper
            'errorOptions' => ['tag' => 'span', 'class' => 'help-inline-block'],
            'template' => "<div class=\"checkbox checkbox-custom checkbox-inline\">{input}\n{label}{error}</div>"
            ])->checkbox(
                [],
                false // IMPORTANT: render input and label separately so template {input}{label} works
            )
        ?>

        <?= $form->field($model, 'enable_annotations_flag', [
            'enableClientValidation' => false,
            'options' => ['tag' => false], // prevent extra wrapper
            'errorOptions' => ['tag' => 'span', 'class' => 'help-inline-block'],
            'template' => "<div class=\"checkbox checkbox-custom checkbox-inline\">{input}\n{label}{error}</div>"
            ])->checkbox(
                ['disabled' => ! $model->has_annotations_flag],
                false // IMPORTANT: render input and label separately so template {input}{label} works
            )
        ?>
        <div class="help-block"></div>

    

    <?php

        $annotation_db_options = array_map(function ($db) {
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
            'limit' => 10, // the maximum times, an element can be cloned (default 999)
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
                        <?php
                            $modelSpacesAnnotations->clearErrors('query');
                            
                            $query = $modelSpacesAnnotations->query;
                            $fieldOptions = [];
                            
                            if (!empty($query)) {
                                $validation = SpacesAnnotations::validateQuerySyntax($query);
                                
                                if (!$validation['valid']) {
                                    $fieldOptions['options'] = ['class' => 'form-group has-error'];
                                    $errorMessages = [];
                                    foreach ($validation['errors'] as $error) {
                                        $errorMessages[] = Html::encode($error);
                                    }
                                    $modelSpacesAnnotations->addError('query', implode('<br>', $errorMessages));
                                }
                            }
                            
                            $field = $form->field($modelSpacesAnnotations, "[{$i}]query", $fieldOptions);
                            
                            if (!empty($query) && isset($validation) && $validation['valid']) {
                                $field = $field->hint('<span style="color: green;">Query validated</span>');
                            }
                            
                            echo $field->textArea(['maxlength' => true, 'class' => 'search-box form-control', 'style' => 'resize: vertical;']);
                        ?>
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
                <div class="row">
                    <div class="col-xs-12">
                        <?= $form->field($modelSpacesAnnotations, "[{$i}]enabled", [
                            'enableClientValidation' => false,
                            'options' => ['tag' => false],
                            'errorOptions' => ['tag' => 'span', 'class' => 'help-inline-block'],
                            'template' => "<div class=\"checkbox checkbox-custom checkbox-inline\">{input}\n{label}{error}</div>"
                        ])->checkbox([], false) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php DynamicFormWidget::end(); ?>

    <h3>Evaluation</h3>

    <div class="form-group">
        <div class="flex-wrap items-center" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
            <label style="margin: 0;">Like/Dislike Records</label>
            <div class="my-switch">
                <?php
                // Get the current value, default to 0 (false) if not set
                $isEnabled = isset($model->enable_like_dislike_records) ? (bool) $model->enable_like_dislike_records : false;
                ?>
                <!-- Hidden input to store the actual value (0 or 1) -->
                <input type="hidden" name="Spaces[enable_like_dislike_records]" id="enable-like-dislike-records-value" value="<?= $isEnabled ? '1' : '0' ?>">
                <input 
                    type="checkbox" 
                    id="enable-like-dislike-records-toggle"
                    class="my-switch-input" 
                    <?= $isEnabled ? 'checked' : '' ?>
                    onchange="document.getElementById('enable-like-dislike-records-value').value = this.checked ? '1' : '0';"
                >
                <label for="enable-like-dislike-records-toggle" class="my-switch-slider"></label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="flex-wrap items-center" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
            <label style="margin: 0;">Confirm/Report Annotations</label>
            <div class="my-switch">
                <?php
                // Get the current value, default to 0 (false) if not set
                $isEnabled = isset($model->enable_like_dislike_annotations) ? (bool) $model->enable_like_dislike_annotations : false;
                ?>
                <!-- Hidden input to store the actual value (0 or 1) -->
                <input type="hidden" name="Spaces[enable_like_dislike_annotations]" id="enable-like-dislike-annotations-value" value="<?= $isEnabled ? '1' : '0' ?>">
                <input 
                    type="checkbox" 
                    id="enable-like-dislike-annotations-toggle"
                    class="my-switch-input" 
                    <?= $isEnabled ? 'checked' : '' ?>
                    onchange="document.getElementById('enable-like-dislike-annotations-value').value = this.checked ? '1' : '0';"
                >
                <label for="enable-like-dislike-annotations-toggle" class="my-switch-slider"></label>
            </div>
        </div>
    </div>


    <div class="form-group">
        <?php if ($model->isNewRecord): ?>
            <?= Html::submitButton('Create', ['class' => 'btn btn-custom-color']) ?>
        <?php else: ?>
            <?= Html::submitButton('Update', ['class' => 'btn btn-success']) ?>
            <?= Html::a('Delete', ['site/admin-delete-spaces', 'space_id' => $model->id], ['class' => 'btn btn-danger']) ?>
        <?php endif ?>


    </div>

    <?php ActiveForm::end(); ?>

</div>



<?= PubmedTypesModal::widget([
    'modalId' => 'spacesPubmedTypesModal',
    'checkboxClass' => 'spaces-pubmed-type-checkbox',
    'checkboxIdPrefix' => 'spaces_pubmed_type_',
    'applyButtonId' => 'spacesSavePubmedTypes',
    'selectedTypes' => $model->pubmed_types,
]) ?>
