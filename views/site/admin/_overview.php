<?php

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;

echo Html::jsFile('@web/js/chartjs_bar_plot_admin.js?v=' . filemtime(Yii::getAlias('@webroot/js/chartjs_bar_plot_admin.js')));
echo Html::jsFile('@web/js/third-party/chartjs/chart_v4.2.0.js');
echo Html::jsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js');
$this->registerCssFile('@web/css/admin-graphs.css');
?>


<div class="row">
    
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">User Statistics</div>
            <div class="panel-body">
                <div class="chart-container">
                    <div class="chart-box">
                        <canvas id="registered_users-bar-plot"></canvas>
                    </div>
                    <div class="chart-box">
                        <canvas id="user-activity-pie-chart"></canvas>
                    </div>
                    <script>
                        render_admin_bar_plot('registered_users-bar-plot', <?= Json::encode($stats->monthly_user_data['labels']) ?>, <?= Json::encode($stats->monthly_user_data['data']) ?>, 'Registered users');
                        render_admin_pie_chart('user-activity-pie-chart', <?= Json::encode($stats->user_activity_data['labels']) ?>, <?= Json::encode($stats->user_activity_data['data']) ?>, 'User activity');
                    </script>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Scholar Statistics</div>
            <div class="panel-body">
                <div class="chart-container">
                    <div class="chart-box">
                        <canvas id="registered_profiles-bar-plot"></canvas>
                    </div>
                    <div class="chart-box">
                        <canvas id="profile-visibility-pie-chart"></canvas>
                    </div>
                    <script>
                        render_admin_bar_plot('registered_profiles-bar-plot', <?= Json::encode($stats->monthly_researcher_data['labels']) ?>, <?= Json::encode($stats->monthly_researcher_data['data']) ?>, 'Registered profiles');
                        render_admin_pie_chart('profile-visibility-pie-chart', <?= Json::encode($stats->researcher_profile_visibility['labels']) ?>, <?= Json::encode($stats->researcher_profile_visibility['data']) ?>, 'Profile visibility');
                    </script>
                </div>
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


