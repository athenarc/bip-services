<?php

namespace app\models;

class ReadingList extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'reading_lists';
    }

    public function rules() {
        return [
            [['title', 'user_id', 'facets', 'is_public'], 'required'],
            [['user_id', 'is_public', 'sort_order'], 'integer'],
            [['title', 'description', 'facets'], 'string'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'Reading List ID',
            'title' => 'Title',
            'description' => 'Description',
            'user_id' => 'Owner',
            'facets' => 'Facets',
            'is_public' => 'Public',
            'sort_order' => 'Sort Order'
        ];
    }
}
