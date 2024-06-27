<?php

namespace app\models;

use Yii;

class ProteinDataBank extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'protein_data_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rcsb_id'], 'required'],
            [['pubmed_id'], 'integer'],
            [['rcsb_id', 'doi'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rcsb_id'  => 'RCSB ID',
            'pubmed_id' => 'Pubmed ID',
            'doi' => 'DOI',
        ];
    }

    public static function findPrimaryCitation($rcsb_id) {

        // retrieve relevant DOI from the rcsb id
        $result = ProteinDataBank::find()->select('doi')->where(['rcsb_id' => $rcsb_id])->one();
        if (empty($result) || empty($result["doi"])) {
            return [];
        }

        // retrive full paper details
        $paper_data = (new \yii\db\Query())
            ->select(['internal_id', 'doi', 'title', 'authors', 'journal', 'year', 'attrank', 'pagerank', '3y_cc'])
            ->from('pmc_paper')
            ->where(['doi' => $result["doi"]])
            ->one();

        // return only DOI (if DOI not found in pmc_paper), else the data of the result
        return (empty($paper_data)) ? ["doi" => $result["doi"]] : $paper_data;

    }
}
