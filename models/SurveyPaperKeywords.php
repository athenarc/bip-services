<?php

namespace app\models;

/**
 * This is the model class for table "survey_paper_keywords".
 *
 * @property int $id
 * @property string $session_id
 * @property int $paper_id
 * @property string $keyword
 * @property string $action
 */
class SurveyPaperKeywords extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'survey_paper_keywords';
    }

    public function rules() {
        return [
            [['action', 'paper_id', 'keywords', 'session_id'], 'required'],
            ['paper_id', 'integer'],
            ['session_id', 'string'],
            [['action'], 'string', 'max' => 10],
            ['keywords', 'string'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'session_id' => 'Session ID',
            'paper_id' => 'Paper ID',
            'keywords' => 'Keywords',
            'action' => 'Action'
        ];
    }
}
