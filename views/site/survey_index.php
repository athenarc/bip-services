<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'BIP! Finder - Survey';
?>
<div class="container site-about">
  <h1><?= Html::encode($this->title) ?></h1>
  <?php if($already_completed){ ?>
    <p>
      <h4>You have already completed the survey! Thank you for your participation.</h4>
    </p>
  <?php } else { ?>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec augue eu lacus elementum ultrices a condimentum massa. Cras vehicula, orci eget lobortis ultrices, ipsum neque consequat ligula, eget eleifend purus ex ac massa. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Fusce a odio nec sapien pharetra varius. Nunc dapibus aliquam ipsum quis laoreet. Donec metus enim, porta sed convallis in, congue et tortor. Donec non nunc nec quam gravida luctus. Morbi quis hendrerit lectus, imperdiet interdum orci. Phasellus vulputate erat non lorem venenatis, vitae tristique massa tempor. Sed accumsan libero id turpis maximus gravida. Sed dignissim hendrerit lacus at pharetra. Sed et condimentum elit, id sagittis eros.
    </p>
    <p>  
      Nullam elit massa, eleifend vitae lacinia in, elementum id purus. Curabitur pellentesque lacinia dapibus. Sed nec cursus orci, id condimentum risus. Nulla euismod nunc et nulla efficitur tincidunt. Phasellus efficitur tempor nisi, sed pulvinar lectus varius non. Integer lacinia tempus sapien, eget dictum nibh hendrerit ut. Sed semper dapibus quam. Nunc feugiat libero velit, ac pharetra neque pulvinar eget. Cras ut velit ut lectus pharetra tempus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Suspendisse luctus dictum gravida. Quisque nec velit in tortor elementum rutrum.
    </p>
    <p>
      Vestibulum rutrum urna mi, ut tincidunt nisi sagittis vitae. Etiam gravida lacus nec massa volutpat, at mattis sapien fringilla. Phasellus pulvinar tristique purus. Quisque a metus eget urna molestie faucibus. Etiam tristique nisi a efficitur fermentum. Pellentesque sagittis lorem nec ipsum semper venenatis. Proin elementum ipsum tincidunt enim facilisis, ultricies porttitor augue vulputate. Vestibulum eleifend nisl sit amet tristique dapibus. Ut ac commodo quam. Pellentesque tellus dui, sollicitudin non est at, suscipit pulvinar eros.
    </p>
    <p>
      Donec tincidunt convallis tempor. Ut euismod sem eget odio ultricies, non ultrices tellus vulputate. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nullam efficitur, lectus a tincidunt pellentesque, purus arcu hendrerit sapien, sit amet facilisis orci risus sed ante. Phasellus a posuere nisl, at molestie tellus. Quisque nec eros ut odio vestibulum maximus at in ligula. Donec ligula tellus, tincidunt at commodo vitae, lobortis eget lectus. Nunc pharetra magna posuere sapien tempus, quis fermentum nisi cursus. Phasellus vel finibus libero. In hac habitasse platea dictumst. Aenean tincidunt odio et ipsum efficitur maximus. Praesent dapibus sodales placerat. Ut vel mollis nulla. Integer suscipit volutpat egestas.
    </p>
    <p>
      Morbi at fermentum sem. Nullam sit amet augue non diam facilisis pretium ut ut dui. Nullam maximus erat vel pharetra mollis. Nunc pretium nunc ut ante rhoncus dapibus. Proin mollis laoreet purus, in tempus tellus vulputate eget. Sed a diam quis massa laoreet dictum sit amet at metus. Pellentesque vel tincidunt nisl. Etiam lacinia sapien ac odio tempor tristique.
    </p>
    <center><a href="<?=  Url::to(['site/survey', 'step' => 1]) ?>" class="btn btn-success btn-primary"><i class="fa fa-play-circle" aria-hidden="true"></i> Start</a></center>  
  <?php } ?>
</div>
