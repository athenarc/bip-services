<?php
use yii\helpers\Html;

echo Html::jsFile('@web/js/third-party/d3/d3.js');
echo Html::jsFile('@web/js/topicEvolutionChart.js');
echo Html::cssFile('@web/css/template/green-black.css');

if (empty($topics_evolution)) {
    echo '<p class="grey-text">No topic evolution data available.</p>';
    return;
}

// Initialize citations if not set
if (!isset($topics_citations)) {
    $topics_citations = [];
}

// Check if citations data is available - show diagram if we have citation data structure
// (even if all values are zero, we still want to show the diagram)
$has_citations = !empty($topics_citations) && is_array($topics_citations);

// Get all years from the first topic (all topics should have the same years after processing)
$first_topic = reset($topics_evolution);
$all_years = array_keys($first_topic);
sort($all_years); // Ensure years are sorted

// Get only the last 10 years
$years = array_slice($all_years, -10);

// Calculate total counts per topic across all years
$topic_totals = [];
foreach ($topics_evolution as $topic_name => $count_per_year) {
    $topic_totals[$topic_name] = array_sum($count_per_year);
}

// Sort by total count descending and get top 5
arsort($topic_totals);
$top_5_topics = array_slice(array_keys($topic_totals), 0, 5);

// Prepare data: array of {year, topic, count}
$sankey_data = [];
foreach ($years as $year) {
    foreach ($top_5_topics as $topic_name) {
        $count = $topics_evolution[$topic_name][$year] ?? 0;
        $sankey_data[] = [
            'year' => (int)$year,
            'topic' => $topic_name,
            'count' => $count
        ];
    }
}

// Colors for ribbons (one per topic)
$colors = [
    'rgba(75, 192, 192, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(255, 99, 132, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(153, 102, 255, 0.7)',
];

$sankey_data_json = json_encode($sankey_data);
$years_json = json_encode($years);
$topics_json = json_encode($top_5_topics);
$colors_json = json_encode($colors);
?>

<div id="top-topics-sankey" style="position:relative; height:500px; width:100%"></div>

<?php if ($has_citations): ?>
<div id="top-topics-citations-sankey" style="position:relative; height:500px; width:100%; margin-top: 10px;"></div>
<?php endif; ?>

<script>
// Render first diagram: Publication counts
renderTopicEvolutionChart({
    containerId: 'top-topics-sankey',
    data: <?= $sankey_data_json ?>,
    years: <?= $years_json ?>,
    topics: <?= $topics_json ?>,
    baseColors: <?= $colors_json ?>,
    yAxisLabel: 'Number of research products',
    valueLabel: 'papers',
    height: 500,
    showLegend: true
});

<?php if ($has_citations): ?>
<?php
// Prepare citation data: array of {year, topic, count}
$sankey_citations_data = [];
foreach ($years as $year) {
    foreach ($top_5_topics as $topic_name) {
        $citation_count = $topics_citations[$topic_name][$year] ?? 0;
        $sankey_citations_data[] = [
            'year' => (int)$year,
            'topic' => $topic_name,
            'count' => $citation_count
        ];
    }
}

$sankey_citations_data_json = json_encode($sankey_citations_data);
?>

// Render second diagram: Citation counts
renderTopicEvolutionChart({
    containerId: 'top-topics-citations-sankey',
    data: <?= $sankey_citations_data_json ?>,
    years: <?= $years_json ?>,
    topics: <?= $topics_json ?>,
    baseColors: <?= $colors_json ?>,
    yAxisLabel: 'Citation count',
    valueLabel: 'citations',
    height: 500,
    showLegend: false
});
<?php endif; ?>
</script>
