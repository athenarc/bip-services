<?php

namespace app\models;

class SavedReadingList extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'saved_reading_lists';
    }

    public function rules() {
        return [
            [['reading_list_id', 'user_id'], 'required'],
            [['reading_list_id', 'user_id', 'sort_order'], 'integer'],
            [['reading_list_id', 'user_id'], 'unique', 'targetAttribute' => ['reading_list_id', 'user_id']],
        ];
    }
}
