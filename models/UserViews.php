<?php

namespace app\models;

/**
 * This is the model class for table "user_views".
 *
 * @property int $user_id
 * @property int $paper_id
 */
class UserViews extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'user_views';
    }

    public function rules() {
        return [
            [['user_id', 'paper_id'], 'required'],
            [['user_id', 'paper_id'], 'integer'],
        ];
    }

    public function attributeLabels() {
        return [
            'user_id' => 'User ID',
            'paper_id' => 'Paper ID',
        ];
    }

    /*
     * Declare a relation to user_likes (there will be an article id in the likes)
     */
    public function getPaper() {
        return $this->hasOne(Article::className(), ['paper_id' => 'pmc']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'id']);
    }
}
