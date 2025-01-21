<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%relations}}".
 *
 * @property string|null $source
 * @property string|null $target
 * @property string|null $relation_name
 */
class Relations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%relations}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source', 'target'], 'string', 'max' => 60],
            [['relation_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'source' => 'Source',
            'target' => 'Target',
            'relation_name' => 'Relation Name',
        ];
    }

    public static function getRelations($papers)
    {
        $internal_ids = array_column($papers, 'internal_id');
        $openaire_ids_source = array_column($papers, 'openaire_id');

        $papers_relations = (new \yii\db\Query())
        ->select(['p.internal_id', 'r.source', 'r.target'])
        ->from(['p' => 'pmc_paper'])
        ->innerJoin(['r' => 'relations'], 'p.openaire_id = r.source')
        ->where(['p.internal_id' => $internal_ids])
        ->all();


        $openaire_ids_target = array_unique(array_column($papers_relations, 'target'));

        $targets_data = (new \yii\db\Query())
        ->select(['r.target', 'p.doi', 'p.type'])
        ->from(['r' => 'relations'])
        ->innerJoin(['p' => 'pmc_paper'], 'p.openaire_id = r.target')
        ->where(['p.openaire_id' => $openaire_ids_target])
        ->all();


        $targetLookup = [];
        foreach ($targets_data as $item) {
            $targetLookup[$item['target']] = $item;
        }
    
        // Create papers_relations_targets table with only matching items from papers_relations and targets_data and add doi/type from the 2nd.
        $papers_relations_targets = [];
        foreach ($papers_relations as $item1) {
            $target = $item1['target'];
            if (isset($targetLookup[$target])) {
                $papers_relations_targets[] = array_merge($item1, [
                    'doi' => $targetLookup[$target]['doi'],
                    // 'type' => $targetLookup[$target]['type'],
                    'type' => Yii::$app->params['work_types'][$targetLookup[$target]['type']]['name']
                ]);
            }
        }

        // Group by internal_id
        $final_array = self::groupByInternalIdAndType($papers_relations_targets);


        foreach ($papers as $paper => $paper_data){

            $id = $paper_data["internal_id"];
            $relations = (array_key_exists($id, $final_array)) ? $final_array[$id] : [];
            // Create relations key to input array
            $papers[$paper]["relations"] = $relations;
        }

        return $papers;
    }

    public static function groupByInternalIdAndType($array) {
        $result = [];

        foreach ($array as $item) {
            $internalId = $item['internal_id'];
            $doi = $item['doi'];
            $type = $item['type'];
    
            // Initialize the structure for the internal_id if not already done
            if (!isset($result[$internalId])) {
                $result[$internalId] = [];
            }
    
            // Find if there is already an entry with the same type
            $found = false;
            foreach ($result[$internalId] as &$group) {
                if ($group['type'] === $type) {
                    // Check for duplicate DOI and append only if not already present
                    if (!in_array($doi, $group['target_dois'])) {
                        $group['target_dois'][] = $doi;
                    }
                    $found = true;
                    break;
                }
            }
    
            // If no group with the same type exists, create a new one
            if (!$found) {
                $result[$internalId][] = [
                    'target_dois' => [$doi],
                    'type' => $type,
                ];
            }
        }
    
        return $result;
    }
}
