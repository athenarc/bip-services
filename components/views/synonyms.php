<?php
use yii\helpers\Html;
use yii\helpers\Url;

?>

<div id="synonyms" class="row" style="margin-top: 15px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body grey-text" style="text-align: left;">
                <?php
                // Build expanded keywords with OR logic
                $expandedKeywords = trim($current_keywords ?? '');
                $synonymTermsForQuery = [];
                $synonymTermsForDisplay = [];

                foreach ($synonyms as $synonym) {
                    $synonymTermsForQuery[] = $synonym; // Use original for query
                    $synonymTermsForDisplay[] = Html::encode($synonym); // Encode for display
                }

                // Build OR query: original_keywords OR synonym1 OR synonym2 OR ...
                if (! empty($expandedKeywords) && ! empty($synonymTermsForQuery)) {
                    $expandedKeywords = $expandedKeywords . ' OR ' . implode(' OR ', $synonymTermsForQuery);
                } elseif (! empty($synonymTermsForQuery)) {
                    $expandedKeywords = implode(' OR ', $synonymTermsForQuery);
                }

                // Start with current params to preserve all filters
                $searchParams = $current_params ?? [];

                // Update keywords with OR-expanded version
                $searchParams['keywords'] = $expandedKeywords;

                // Ensure space_url_suffix is set
                if (! empty($space_url_suffix)) {
                    $searchParams['space_url_suffix'] = $space_url_suffix;
                }

                $searchUrl = Url::to(array_merge(['site/index'], $searchParams));
                $synonymsText = implode(', ', $synonymTermsForDisplay);
                $entityDisplayName = ! empty($entity_name) ? Html::encode($entity_name) : 'entity';
                ?>
                Looking for a <?= $entityDisplayName ?>? You can broaden your search by including related terms and identifiers such as: <i><?= $synonymsText ?></i>
                <?= Html::a('<i class="fa fa-play" aria-hidden="true"></i> Expand search', $searchUrl, ['class' => 'btn btn-custom-color btn-xs', 'style' => 'margin-left: 10px;', 'encode' => false]) ?>
            </div>
        </div>
    </div>
</div>
