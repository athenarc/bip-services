<?php

use yii\helpers\Html;

function renderSection($title, $facetKey, $result, $icon) {
    if (isset($result["facets"][$facetKey])) {
        $counts = $result["facets"][$facetKey]["counts"];
        $options = $result["facets"][$facetKey]["options"];
        echo "<div class='section'>";
        echo "<div class='title'><i class='$icon' aria-hidden='true'></i> $title</div>";
        echo "<ul>";
        foreach ($options as $value => $label) {
            $count = $counts[$value] ?? 0;
            $label_text = is_array($label) ? $label["name"] : $label;
            echo "<li>$label_text <span class='count'>$count</span></li>";
        }
        echo "</ul>";
        echo "</div>";
    }
}

?>

<div>
    <div>
        <?php if (isset($current_cv_narrative)): ?>
            <h4 id="current_cv_narrative_title">
                <?= Html::encode($current_cv_narrative->title) ?>
            </h4>
            <div id="current_cv_narrative_description">
                <?= Html::encode($current_cv_narrative->description) ?>
            </div>
        <?php else: ?>
            <?php 
            $sections = [
                'Topics' => ['facetKey' => 'topics', 'icon' => 'fa-solid fa-atom'],
                'Roles' => ['facetKey' => 'roles', 'icon' => 'fa fa-briefcase'],
                'Availability' => ['facetKey' => 'accesses', 'icon' => 'fas fa-lock-open'],
                'Work type' => ['facetKey' => 'types', 'icon' => 'fas fa-cube']
            ];
            foreach ($sections as $title => $data) {
                if (isset($element_config[$title])) {
                    renderSection($title, $data['facetKey'], $result, $data['icon']);
                }
            }
            ?>
        <?php endif; ?>
    </div>
</div>