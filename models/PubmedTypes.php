<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%pubmed_types}}".
 *
 * @property string $pmid
 * @property string $pubmed_types
 */
class PubmedTypes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%pubmed_types}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pmid', 'pubmed_types'], 'required'],
            [['pmid'], 'string', 'max' => 20],
            [['pubmed_types'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'pmid' => 'Pmid',
            'pubmed_types' => 'NLM Types',
        ];
    }


    public static function getPubmedTypes($papers, $space_model){

        // in space that does not support pubmed_types
        if (isset($space_model->url_suffix) && !$space_model->has_pubmed_types){

            foreach ($papers as $paper => $paper_data){
                // add empty entry
                $papers[$paper]["pubmed_types"] = [];
            }

            return $papers;
        }

        // out of space or in space that supports pubmed_types

        $ids = array_column($papers, 'internal_id');

        // each paper can have multiple pmids
        $ids_to_types = (new \yii\db\Query())
        ->select('pp.paper_id, pt.pmid, pt.pubmed_types')
        ->from('pmc_paper_pids pp')
        ->innerJoin('pubmed_types pt', 'pp.doi = pt.pmid')
        ->where(['paper_id' => $ids])
        ->all();




        // Group by paper_id
        $id_to_types_group = [];

        foreach ($ids_to_types as $row) {
            $paperId = $row['paper_id'];
            $types = explode(',', $row['pubmed_types']);

            foreach ($types as $type) {

                // avoid adding duplicate pubmed_types from multiple pmids
                if (isset($id_to_types_group[$paperId]) && in_array($type, array_column($id_to_types_group[$paperId], 'id'), true)) {
                    continue;
                }

                $id_to_types_group[$paperId][] = [
                    'id' => $type,
                    'name' =>  Yii::$app->params['pubmed_types_fields'][$type] ?? null,
                ];
            }
        }


        foreach ($papers as $paper => $paper_data){

            $id = $paper_data['internal_id'];
            $types = (array_key_exists($id, $id_to_types_group)) ? $id_to_types_group[$id] : [];
            // Create pubmed_types key to input array
            $papers[$paper]["pubmed_types"] = $types;
        }


        return $papers;
    }

}
