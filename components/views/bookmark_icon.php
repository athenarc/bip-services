<?php

/*
 * Bookmark icon view
 *
 * (First Version: Aug 2021)
 *
 */


use yii\helpers\Html;
use yii\helpers\Url;


$remove_text = 'Remove from my readings';
$add_text = 'Add to my readings';

?>



<?php if ($user_liked != null && $user_logged != ''): ?>
    <?= Html::a('<i class="fa-solid fa-bookmark liked-heart" aria-hidden="true"></i>', '#', ['class' => 'my-btn', 'id'=>'a_res_' . $id_bookmark, 'title' => $remove_text]); ?>
<?php elseif ($user_logged == ''): ?>
    <?= Html::a('<i class="fa-regular fa-bookmark" aria-hidden="true"></i>', Url::to(['site/likeunavailable']), ['data-method' => 'post', 'class' => 'my-btn', 'id' => $id_bookmark, 'title' => $add_text]); ?>
<?php else: ?>
    <?= Html::a('<i class="fa-regular fa-bookmark not-liked-heart" aria-hidden="true"></i>', '#' , ['class' => 'my-btn', 'id'=>'a_res_' . $id_bookmark, 'title' => $add_text]); ?>
<?php endif; ?>

