<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "survey_paper_keywords".
 *
 * @property integer $id
 * @property string $session_id
 * @property integer $paper_id
 * @property string $keyword
 * @property string $action
 */
class SurveyPaperKeywords extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'survey_paper_keywords';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action', 'paper_id', 'keywords', 'session_id'], 'required'],
            ['paper_id', 'integer'],
            ['session_id', 'string'],
            [['action'], 'string', 'max' => 10],
            ['keywords', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'session_id' => 'Session ID',
            'paper_id' => 'Paper ID',
            'keywords' => 'Keywords',
            'action' => 'Action'
        ];
    }
}