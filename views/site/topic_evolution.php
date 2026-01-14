<?php
    use yii\helpers\Html;

    echo Html::jsFile('@web/js/chartjs_bar_plot.js?v=' . filemtime(Yii::getAlias('@webroot/js/chartjs_bar_plot.js')));
    echo Html::jsFile('@web/js/third-party/chartjs/chart_v4.2.0.js');
    echo Html::jsFile('@web/js/third-party/chartjs/chart_labels_v2.2.0.js');
    echo Html::cssFile('@web/css/template/green-black.css');

    // Convert data to JSON for use in JavaScript
    $years = json_encode(array_keys($count_per_year));
    $counts = json_encode(array_values($count_per_year));
    $citation_per_year = json_encode(array_values($citation_per_year));

?>

<div style="margin-bottom: 20px;">
    <h4 class="grey-text" style="margin-bottom: 10px;">Number of research products per year</h4>
    <div style="position:relative; height:100%; width:100%">
        <canvas id="topic-evolution-bar-plot"></canvas>
    </div>
</div>
<div style="margin-bottom: 20px;">
    <h4 class="grey-text" style="margin-bottom: 10px;">Citation count per year</h4>
    <div style="position:relative; height:100%; width:100%">
        <canvas id="citations-per-year-bar-plot"></canvas>
    </div>
</div>

<script>
    render_bar_plot('topic-evolution-bar-plot', <?= $years ?>, <?= $counts ?>, 'Number of research products', 'Research products per year', 'Publication year');
    render_bar_plot('citations-per-year-bar-plot', <?= $years ?>, <?= $citation_per_year ?>, 'Citation count', 'Citation count', 'Publication year');
</script>