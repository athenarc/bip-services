<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Journal Model
 *
 */
class Journal extends Model
{
	public function autocomplete($term, $count) {
		$query = Yii::$app->solr->createSuggester();

		$query->setQuery($term);
		$query->setDictionary('journalSuggester');
		$query->setCount($count);

		// this executes the query and returns the result
		$resultset = Yii::$app->solr->suggester($query);

		$journal_names = [];

		foreach ($resultset as $dictionary => $terms) {
		    foreach ($terms as $term => $termResult) {
		        foreach ($termResult as $result) {
		            array_push($journal_names, $result['term']);
		        }
		    }
		}

		return $journal_names;
	}
}