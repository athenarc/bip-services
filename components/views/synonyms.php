<?php
use yii\helpers\Html;

// Wrap each term in parentheses only if it contains multiple words (spaces)
$wrapIfMultipleWords = function ($term) {
    return (strpos(trim($term), ' ') !== false) ? '(' . $term . ')' : $term;
};

// Function to build expanded keywords for a specific set of synonyms
$buildExpandedKeywords = function ($keywords, $synonymTerms) use ($wrapIfMultipleWords) {
    $expandedKeywords = trim($keywords ?? '');
    $wrappedSynonyms = ! empty($synonymTerms) ? array_map($wrapIfMultipleWords, $synonymTerms) : [];

    if (! empty($expandedKeywords) && ! empty($wrappedSynonyms)) {
        return $wrapIfMultipleWords($expandedKeywords) . ' OR ' . implode(' OR ', $wrappedSynonyms);
    } elseif (! empty($wrappedSynonyms)) {
        return implode(' OR ', $wrappedSynonyms);
    }

    return ! empty($expandedKeywords) ? $wrapIfMultipleWords($expandedKeywords) : '';
};

?>

<div id="synonyms" class="row" style="margin-top: 15px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body grey-text" style="text-align: left;">
                <?php
                foreach ($synonyms_expansions as $index => $expansion) {
                    $expansionSynonyms = $expansion['synonyms'] ?? [];
                    $displayName = Html::encode($expansion['display_name'] ?? 'entity');

                    if (! empty($expansionSynonyms)) {
                        $expansionTermsForDisplay = array_map(function ($syn) {
                            return Html::encode($syn);
                        }, $expansionSynonyms);

                        $synonymsText = implode(', ', $expansionTermsForDisplay);

                        // Build expanded keywords for this specific expansion
                        $expandedKeywords = $buildExpandedKeywords($current_keywords, $expansionSynonyms); ?>
                        <div style="<?= $index > 0 ? 'margin-top: 15px;' : '' ?>">
                            Looking for a <?= $displayName ?>? You can broaden your search by including related terms and identifiers such as: <i><?= $synonymsText ?></i>
                            <button type="button" class="btn btn-custom-color btn-xs expand-search-btn" style="margin-left: 10px;" data-expanded-keywords="<?= Html::encode($expandedKeywords) ?>">
                                <i class="fa fa-play" aria-hidden="true"></i> Expand search
                            </button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
