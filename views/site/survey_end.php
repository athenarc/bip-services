<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'BIP! Finder - Survey Finished';
?>
<div class="container site-about">
  <h1><?= Html::encode($this->title) ?></h1>

  <p>
      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec augue eu lacus elementum ultrices a condimentum massa. Cras vehicula, orci eget lobortis ultrices, ipsum neque consequat ligula, eget eleifend purus ex ac massa. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Fusce a odio nec sapien pharetra varius. Nunc dapibus aliquam ipsum quis laoreet. Donec metus enim, porta sed convallis in, congue et tortor. Donec non nunc nec quam gravida luctus. Morbi quis hendrerit lectus, imperdiet interdum orci. Phasellus vulputate erat non lorem venenatis, vitae tristique massa tempor. Sed accumsan libero id turpis maximus gravida. Sed dignissim hendrerit lacus at pharetra. Sed et condimentum elit, id sagittis eros.
  </p>
  <p>  
    Nullam elit massa, eleifend vitae lacinia in, elementum id purus. Curabitur pellentesque lacinia dapibus. Sed nec cursus orci, id condimentum risus. Nulla euismod nunc et nulla efficitur tincidunt. Phasellus efficitur tempor nisi, sed pulvinar lectus varius non. Integer lacinia tempus sapien, eget dictum nibh hendrerit ut. Sed semper dapibus quam. Nunc feugiat libero velit, ac pharetra neque pulvinar eget. Cras ut velit ut lectus pharetra tempus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Suspendisse luctus dictum gravida. Quisque nec velit in tortor elementum rutrum.
  </p>
  <?php $form = ActiveForm::begin(['id' => 'credits-form', 'action'=> Url::to(['site/save-survey-credits'])]); ?>

    <?= $form->field($credits_model, 'name') ?>
    <?= $form->field($credits_model, 'email') ?>
    <?= $form->field($credits_model, 'affiliation') ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>
</div>
