<?php

namespace app\models;

use Yii;

class ReadingList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reading_lists';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'user_id', 'facets', 'is_public'], 'required'],
            [['user_id', 'is_public'], 'integer'],
            [['title', 'description', 'facets'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'  => 'Reading List ID',
            'title' => 'Title',
            'description' => 'Description',
            'user_id' => 'Owner',
            'facets' => 'Facets',
            'is_public' => 'Public'
        ];
    }
}
