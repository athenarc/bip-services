<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\web\View;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;

$this->title = 'BIP! Services - Scholar';

?>

<div id="scholar-landing">
  <div class="jumbotron text-center">
    <h2 class="display-4">BIP! Scholar Profiles</h2>
    <p class="lead">Hightlight key points of your research and frame them into context</p>
  </div>
  <div class="text-center" style="margin: 10px 0px">
      <a href="<?= Url::to(['scholar/profile']) ?>" class="btn btn-custom-color btn-lg">
      <?php
      if (Yii::$app->user->isGuest) {
          echo "Sign In";
      } elseif ( !isset($researcher->orcid ) ) {
          echo "Create Profile";
      } else {
          echo "<i class='fa-regular fa-user'></i> My profile";
      }
      ?>
      </a>

      <a href="<?= Url::to(['scholar/search']) ?>" class="btn btn-default btn-lg">
        <i class="fa-solid fa-magnifying-glass"></i> Search profiles
      </a>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="card">
        <div class="card-body">
          <h3 class="card-title">Enriched ORCID-Based Profiles</h2>
          <p class="card-text">BIP! Scholar offers researchers the option to create ORCID-based academic profiles enriched with valuable additional information (e.g., CRediT roles, indicators, narratives).</p>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="card">
        <div class="card-body">
          <h3 class="card-title">Put your work into context with narratives</h2>
          <p class="card-text">BIP! Scholar helps researchers in creating narratives that describe interesting lines of work providing valuable information about the respective impact and the related activities and skills.</p>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-10 col-md-offset-1">
      <div class="card">
        <div class="card-body">
          <h3 class="card-title">Explore your work from different perspectives</h2>
          <p class="card-text">BIP! Scholar can help the exploration of different perspectives of a researcher's career: tailored views of each profile can be dynamically produced according to particular topics, roles, work types, and so on.</p>
        </div>
      </div>
    </div>
  </div>
  </div>
</div>
