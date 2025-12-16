<?php

namespace app\models;

class Tags extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'tags';
    }

    public function rules() {
        return [
            [['name', 'used'], 'required'],
            [['id', 'used'], 'integer'],
            [['name'], 'string'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'Tag ID',
            'name' => 'Tag Name',
            'used' => 'Times used',
        ];
    }
}
