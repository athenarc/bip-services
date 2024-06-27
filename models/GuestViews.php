<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "guest_views".
 *
 * @property integer $guest_ip
 * @property integer $paper_id
 */
class GuestViews extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'guest_views';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['guest_ip', 'paper_id'], 'required'],
            ['paper_id', 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'guest_ip' => 'Guest Ip',
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
      
}
