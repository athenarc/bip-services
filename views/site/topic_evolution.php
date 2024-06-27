<?php
    use yii\helpers\Html;

    echo Html::jsFile('@web/js/chartjs_bar_plot.js');
    echo Html::jsFile('@web/js/third-party/chartjs/chart_v4.2.0.js');
    echo Html::jsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js');
    echo Html::cssFile('@web/css/template/green-black.css');

    // Convert data to JSON for use in JavaScript
    $years = json_encode(array_keys($data));
    $counts = json_encode(array_values($data));
?>

<div style="position:relative; height:100%; width:100%">
    <canvas id="topic-evolution-bar-plot"></canvas>
</div>
<script>
    render_bar_plot('topic-evolution-bar-plot', <?= json_encode(array_keys($data)) ?>, <?= json_encode(array_values($data)) ?>);
</script>