<?php
    use yii\helpers\Html;

    echo Html::jsFile('@web/js/third-party/chartjs/chart_v4.2.0.js');
    echo Html::jsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js');
    echo Html::jsFile('@web/js/chartjs_polar_area.js');
    echo Html::cssFile('@web/css/third-party/fontawesome-free-6.0.0-web/css/all.min.css');
    echo Html::cssFile('@web/css/template/green-black.css');
?>

<div style="position:relative; height:100%; width:100%">
    <canvas id="chart"></canvas>
</div>
<script>
    render_polar_area_chart('chart', '', <?= json_encode($chart_data['data']) ?>, <?= json_encode($chart_data['tooltips']) ?>);
</script>
