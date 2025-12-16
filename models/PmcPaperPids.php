<?php

namespace app\models;

/**
 * This is the model class for table "{{%pmc_paper_pids}}".
 *
 * @property int $paper_id
 * @property string $openaire_id
 * @property string $doi
 */
class PmcPaperPids extends \yii\db\ActiveRecord {
    public static function tableName() {
        return '{{%pmc_paper_pids}}';
    }

    public function rules() {
        return [
            [['paper_id', 'openaire_id', 'doi'], 'required'],
            [['paper_id'], 'integer'],
            [['openaire_id'], 'string', 'max' => 60],
            [['doi'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels() {
        return [
            'paper_id' => 'Paper ID',
            'openaire_id' => 'Openaire ID',
            'doi' => 'Doi',
        ];
    }

    /**
     * Gets the related paper.
     * $pid = PmcPaperPids::find()->where(['doi' => '10.1000/example.doi'])->one();
     * $paper = $pid?->paper;
     * Each pid belongs to one paper.
     * @return \yii\db\ActiveQuery
     */
    public function getPaper() {
        return $this->hasOne(Article::class, ['internal_id' => 'paper_id']);
    }
}
