<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<div id="synonyms" class="row grey-text" style="margin-top: 15px;">
    <div class="col-md-12" style="text-align: left;">
        <?php
        $synonymLinks = [];
        foreach ($synonyms as $synonym) {
            // Combine current keywords with the synonym
            $expandedKeywords = trim($current_keywords ?? '');
            if (!empty($expandedKeywords)) {
                $expandedKeywords .= ' ' . $synonym;
            } else {
                $expandedKeywords = $synonym;
            }
            
            // Start with current params to preserve all filters
            $searchParams = $current_params ?? [];
            
            // Update keywords
            $searchParams['keywords'] = $expandedKeywords;
            
            // Ensure space_url_suffix is set
            if (!empty($space_url_suffix)) {
                $searchParams['space_url_suffix'] = $space_url_suffix;
            }
            
            $searchUrl = Url::to(array_merge(['site/index'], $searchParams));
            $synonymLinks[] = Html::a(Html::encode($synonym), $searchUrl, ['class' => 'main-green']);
        }
        
        $entityDisplayName = !empty($entity_name) ? Html::encode($entity_name) : 'entity';
        $synonymsText = implode(', ', $synonymLinks);
        ?>
        Looking for a <?= $entityDisplayName ?>? You can broaden your search by including related terms and identifiers such as <?= $synonymsText ?>
    </div>
</div>
