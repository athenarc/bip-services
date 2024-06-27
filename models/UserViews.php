<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_views".
 *
 * @property integer $user_id
 * @property integer $paper_id
 */
class UserViews extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_views';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'paper_id'], 'required'],
            [['user_id', 'paper_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'paper_id' => 'Paper ID',
        ];
    }
    
    /*
     * Declare a relation to user_likes (there will be an article id in the likes)
     */
    public function getPaper()
    {
        return $this->hasOne(Article::className(), ['paper_id' => 'pmc']);
    }    
    
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'id']);
    }
    
}
