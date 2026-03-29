<?php

namespace app\components;

class ArticleHelper
{

/**
 * Resolves identifier or returns null.
 *
 * @param  string|null $value Identifier value.
 *
 * @return array|null
 */
	public static function resolvePid($value) {
		// rule for Doi
		if (str_starts_with($value, '10.')) {
			return [
				'label' => 'DOI',
				'url'   => 'https://doi.org/' . $value,
				'value' => $value,
			];
		}

		// rule for PubMed
		if (ctype_digit($value) || str_starts_with($value, 'PMC')) {
			return [
				'label' => 'PubMed Id',
				'url'   => 'https://www.ncbi.nlm.nih.gov/pubmed/' . $value,
				'value' => $value,
			];
		}

		// Anything else, ignore
		return null;
	}
}