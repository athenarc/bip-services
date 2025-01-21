<?php

namespace app\models;

use Yii;

class CvNarrative extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cv_narratives';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'user_id', 'description', 'papers'], 'required'],
            [['user_id'], 'integer'],
            [['title', 'description', 'papers'], 'string'],
            [['title'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'  => 'CV Narrative ID',
            'title' => 'Title',
            'description' => 'Description',
            'user_id' => 'Owner',
            'papers' => 'Paper',
        ];
    }


}
