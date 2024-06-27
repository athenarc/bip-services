<?php

namespace app\models;

use Yii;

class Tags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'used'], 'required'],
            [['id', 'used'], 'integer'],
            [['name'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'  => 'Tag ID',
            'name' => 'Tag Name',
            'used' => 'Times used',
        ];
    }
}
