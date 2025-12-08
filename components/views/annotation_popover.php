<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\LikeDislikeAnnotations;

?>

<?php 
// Annotation id is provided at the top level
$annotation_id = $this->context->annotation_id ?? null;
foreach($this->context->data as $annotation_data): ?>
    <div>
        <span class='green-bip'><?= ucfirst($annotation_data['label']) ?>:</span>
        <?= empty(($annotation_data['value'])) ? 'N/A' : ucfirst(str_replace("\"", "'", $annotation_data['value'])) ?>
    </div>
<?php endforeach; ?>
    <div>
        <span class='green-bip'><?= 'Source' ?>:</span>
        <?= str_replace("\"", "'",  Yii::$app->params['annotation_dbs'][$this->context->space_annotation_db]['name'] . ' knowledge graph') ?>
    </div>

    <?php if ($this->context->has_reverse_annotation_query): ?>
        <div>
            <span class='green-bip'> All relevant works:</span>
            <a href='<?= Url::to(['site/annotation', 'annotation_id' => $annotation_id, 'space_url_suffix' => $this->context->space_url_suffix, 'space_annotation_id' => $this->context->space_annotation_id]) ?>' target='_blank'><i class='fa-solid fa-arrow-up-right-from-square'></i></a>
        </div>
    <?php endif; ?>

    <?php 
    
    // Show like/dislike buttons if enabled for this space
    
    $show_like_dislike = !Yii::$app->user->isGuest 
        && isset($this->context->enable_like_dislike_annotations) 
        && (bool)$this->context->enable_like_dislike_annotations
        && isset($this->context->paper_id) 
        && isset($this->context->annotation_name)
        && isset($annotation_id);
    
    if ($show_like_dislike): 
        $paper_id = $this->context->paper_id;
        $annotation_name = $this->context->annotation_name;
        $space_url_suffix = $this->context->space_url_suffix;

        // Get user vote (server-side) or use provided value
        $user_vote_annotation = $this->context->user_vote_annotation 
            ?? LikeDislikeAnnotations::getUserVote(Yii::$app->user->id, $paper_id, $annotation_id, $space_url_suffix);

        // Determine button classes & styles based on user vote
        $inactive_class = 'btn btn-default grey-link btn-xs';
        $active_class = 'btn btn-xs';
        $active_style = 'style="background-color: var(--main-color); color: white;"';
        
        $like_class = ($user_vote_annotation === 'like') ? $active_class : $inactive_class;
        $like_style = ($user_vote_annotation === 'like') ? $active_style : '';
        $dislike_class = ($user_vote_annotation === 'dislike') ? $active_class : $inactive_class;
        $dislike_style = ($user_vote_annotation === 'dislike') ? $active_style : '';
    ?>
        <div style='margin-top: 5px; padding-top: 5px;'>
            <div class='like-dislike-annotation-buttons' style='text-align: right;'
                 data-paper-id='<?= Html::encode($paper_id) ?>' 
                 data-annotation-id='<?= Html::encode($annotation_id) ?>'
                 data-annotation-name='<?= Html::encode($annotation_name) ?>'
                 data-space-url-suffix='<?= Html::encode($space_url_suffix) ?>'>
                <button class="btn-like-annotation <?= $like_class ?>" type="button" title="Confirm this annotation" <?= $like_style ?>>
                    <i class="fa-solid fa-check"></i>
                </button>
                <button class="btn-dislike-annotation <?= $dislike_class ?>" type="button" title="Report this annotation" <?= $dislike_style ?>>
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

