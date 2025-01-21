<?php

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;


?>


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">User Statistics</div>
                <div class="panel-body">
                <p><strong>Number of Users:</strong> <?= $stats->total_users ?></p>
                </div>
            </div>
      </div>
    <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">Scholar Statistics</div>
            <div class="panel-body">
            <p><strong>Users with profiles:</strong> <?= $stats->total_scholar_profiles ?> (<?= $stats->total_public_scholar_profiles ?> public)</p>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">Readings Statistics</div>
            <div class="panel-body">
            <p><strong>Number of readings:</strong> <?= $stats->total_users_likes ?></p>
            <p><strong>Users with readings:</strong> <?= $stats->total_users_with_likes ?></p>
            </div>
        </div>
    </div>
</div>


