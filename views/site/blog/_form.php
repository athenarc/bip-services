<?php

/* @var $this yii\web\View */
/* @var $model app\models\BlogPost */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

$this->registerJsFile('@web/js/third-party/tinymce_5.10.0/tinymce.min.js', [
    'position' => View::POS_END,
    'depends' => [\yii\web\JqueryAsset::className()],
]);
$this->registerJsFile('@web/js/tinymceBlogPanel.js', [
    'position' => View::POS_END,
    'depends' => [\yii\web\JqueryAsset::className()],
]);

?>

<?php $form = ActiveForm::begin([
    'id' => 'blog-post-form',
    'options' => ['enctype' => 'multipart/form-data'],
    'fieldConfig' => [
        'template' => "{label}\n{input}\n{error}",
        'options' => ['class' => 'form-group'],
    ],
]); ?>

<?= $form->field($model, 'title')->textInput([
    'class' => 'search-box form-control',
    'maxlength' => true,
]) ?>

<?= $form->field($model, 'content')->textarea([
    'class' => 'search-box form-control rich_text_area_blog',
    'rows' => 14,
]) ?>

<?= $form->field($model, 'tags')->textInput([
    'class' => 'search-box form-control',
    'placeholder' => 'news, release, update',
]) ?>

<?= $form->field($model, 'coverUpload')->fileInput([
    'class' => 'form-control',
    'accept' => '.jpg,.jpeg,.png,.webp',
]) ?>
<?php if (! empty($model->cover_image)) : ?>
    <?= Html::activeHiddenInput($model, 'removeCover', ['id' => 'blog-remove-cover-flag', 'value' => 0]) ?>
    <div id="blog-current-cover-row" class="text-muted-settings" style="margin-bottom: 10px;">
        Current cover:
        <a href="<?= Html::encode($model->coverImageUrl) ?>" target="_blank" rel="noopener noreferrer">
            <?= Html::encode($model->cover_image) ?>
        </a>
        <button id="blog-remove-cover-btn"
            type="button"
            class="btn btn-link btn-xs text-danger"
            style="padding: 0 0 0 8px; vertical-align: baseline;"
            title="Remove cover image"
            onclick="if(confirm('Remove current cover image?')){document.getElementById('blog-remove-cover-flag').value='1'; document.getElementById('blog-current-cover-row').style.display='none';}"
        >x</button>
    </div>
<?php endif; ?>

<?= $form->field($model, 'status')->dropDownList($model::getStatusList(), [
    'class' => 'search-box form-control',
]) ?>

<div class="form-group" style="margin-bottom: 0; margin-top: 8px;">
    <?= Html::submitButton($model->isNewRecord ? 'Create post' : 'Save changes', ['class' => 'btn btn-custom-color']) ?>
    <?= Html::a('Cancel', Url::to(['/blog/default/index']), ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>
