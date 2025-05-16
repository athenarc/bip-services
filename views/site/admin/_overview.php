<?php

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;

echo Html::jsFile('@web/js/chartjs_bar_plot_admin.js?v=' . filemtime(Yii::getAlias('@webroot/js/chartjs_bar_plot_admin.js')));
echo Html::jsFile('@web/js/third-party/chartjs/chart_v4.2.0.js');
echo Html::jsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js');
echo Html::cssFile('@web/css/template/green-black.css');

?>


<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">User Statistics</div>
                <div class="panel-body">
                    <p><strong>Number of Users:</strong> <?= $stats->total_users ?></p>
                    <div style="position:relative; height:50%; width:50">
                        <canvas id="registered_users-bar-plot"></canvas>
                    </div>
                    <script>
                        render_admin_bar_plot('registered_users-bar-plot', <?= $graph_labels ?>, <?= $graph_data ?>, 'Registered users');
                    </script>
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


