<?php

namespace app\models;

class CvNarrative extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'cv_narratives';
    }

    public function rules() {
        return [
            [['title', 'user_id', 'description', 'papers'], 'required'],
            [['user_id'], 'integer'],
            [['title', 'description', 'papers'], 'string'],
            [['title'], 'trim'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'CV Narrative ID',
            'title' => 'Title',
            'description' => 'Description',
            'user_id' => 'Owner',
            'papers' => 'Paper',
        ];
    }
}
