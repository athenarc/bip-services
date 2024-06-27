<?php

namespace app\models;

use Yii;
use yii\helpers\Url;
use yii\base\Model;

/**
 *
 *
 */
class Concepts extends Model
{


    public static function getConcepts($papers, $id_column) {

        $ids = array_column($papers, $id_column);

        $ids_to_concepts = (new \yii\db\Query())
        ->select('paper_id, display_name, description, wikidata, concept_score, id')
        ->from('concepts_to_papers')
        ->leftJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
        ->where(['concepts_to_papers.paper_id' => $ids])
        ->orderBy(['paper_id' => SORT_ASC, 'concept_score' => SORT_DESC])
        ->all();

        // Group by paper_id
        $id_to_concepts_scores = \yii\helpers\ArrayHelper::index($ids_to_concepts, null, 'paper_id');

        foreach ($papers as $paper => $paper_data){

            $id = $paper_data[$id_column];
            $concepts = (array_key_exists($id, $id_to_concepts_scores)) ? $id_to_concepts_scores[$id] : [];
            // Create concept key to input array
            $papers[$paper]["concepts"] = $concepts;
        }

        return $papers;
    }

    public static function autocomplete($term, $max_num) {
        $q = (new \yii\db\Query())
            ->select([ 'id', 'display_name' ])
            ->from('concepts')
            ->offset(0)
            ->limit($max_num);

        // get concepts with $term
        $start_with_term = (clone $q)->where([ 'LIKE', 'display_name', $term . '%', false ])->all();

        // get concepts containing term
        $include_term = (clone $q)->where([ 'LIKE', 'display_name', '%' . $term . '%', false ])->all();
        
        // merge results of previous queries
        $res = array_merge($start_with_term, $include_term);

        // keep only $max_num first
        return array_slice(array_column($res, 'display_name', 'id'), 0, $max_num, true);
    }

}
