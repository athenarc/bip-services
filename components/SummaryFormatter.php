<?php

namespace app\components;

use yii\helpers\Html;
use yii\helpers\Url;

class SummaryFormatter {
    public static function formatSummaryWithReferences(string $summary, array $papers): array {
        $plainSummary = $summary;
        $referenceLines = [];
        $referenceUrlByIndex = [];

        foreach ($papers as $i => $paper) {
            $id = (string) ($paper['id'] ?? '');

            if ($id === '') {
                continue;
            }

            $index = $i + 1;
            $url = (string) Url::to(['site/details', 'id' => $paper['doi']], true);
            $plainSummary = str_replace($id, "[${index}]", $plainSummary);
            $referenceUrlByIndex[(string) $index] = $url;

            $title = $paper['title'] ?? 'Untitled';
            $journal = $paper['journal'] ?? 'Unknown Journal';
            $year = $paper['year'] ?? 'n.d.';
            $doi = $paper['doi'] ?? '';
            $doiUrl = $doi ? "https://doi.org/{$doi}" : '';
            $referenceLines[] = "[${index}] ${title}. ${journal}, ${year}. ${doiUrl}";
        }

        $plainSummary = str_replace(['[[', ']]'], ['[', ']'], $plainSummary);
        $bodyPlain = self::prepareDescriptionForDisplay($plainSummary)[0];

        if (! empty($referenceLines)) {
            $plainSummary .= "\n\nReferences:\n" . implode("\n", $referenceLines);
        }

        return [
            // Keep legacy fields for existing consumers.
            'html' => self::renderCitationHtml($bodyPlain, $referenceUrlByIndex),
            'plain' => $plainSummary,
            // Explicit body fields for views that should not display trailing references.
            'body_plain' => $bodyPlain,
            'body_html' => self::renderCitationHtml($bodyPlain, $referenceUrlByIndex),
        ];
    }

    public static function buildLinkedDescriptionHtml(string $description): string {
        [$descriptionBody, $referenceUrlByIndex] = self::prepareDescriptionForDisplay($description);

        return self::renderCitationHtml($descriptionBody, $referenceUrlByIndex);
    }

    public static function getDescriptionBody(string $description): string {
        return self::prepareDescriptionForDisplay($description)[0];
    }

    public static function prepareDescriptionForDisplay(string $description): array {
        $descriptionBody = $description;
        $referenceUrlByIndex = [];

        if (preg_match('/^(.*?)(?:\r?\n){2}References:\r?\n(.*)$/s', $description, $matches)) {
            $descriptionBody = trim($matches[1]);
            $referencesBlock = trim($matches[2]);

            foreach (preg_split('/\r?\n/', $referencesBlock) as $line) {
                if (preg_match('/^\[(\d+)\].*?(https?:\/\/\S+)\s*$/', trim($line), $refMatch)) {
                    $index = $refMatch[1];
                    $referenceUrl = $refMatch[2];

                    // Keep citation links consistent with Finder by pointing to internal details pages.
                    if (preg_match('~https?://(?:dx\.)?doi\.org/(.+)$~i', $referenceUrl, $doiMatch)) {
                        $referenceUrl = (string) Url::to(['site/details', 'id' => urldecode($doiMatch[1])], true);
                    }

                    $referenceUrlByIndex[$index] = $referenceUrl;
                }
            }
        }

        return [$descriptionBody, $referenceUrlByIndex];
    }

    public static function renderCitationHtml(string $text, array $referenceMap): string {
        $escaped = Html::encode($text);
        $withLinks = preg_replace_callback('/\[(\d+)\]/', static function ($match) use ($referenceMap) {
            $index = $match[1];

            if (empty($referenceMap[$index])) {
                return $match[0];
            }

            return '[' . Html::a($index, $referenceMap[$index], [
                'target' => '_blank',
                'rel' => 'noopener noreferrer',
                'class' => 'main-green',
            ]) . ']';
        }, $escaped);

        return nl2br($withLinks);
    }
}
