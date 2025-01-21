<?php

namespace app\models;

use yii\db\ActiveRecord;

class ElementBulletedListItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'element_bulleted_list_item';
    }

    public function rules()
    {
        return [
            [['user_id', 'template_id', 'element_id'], 'required'],
            [['user_id', 'template_id', 'element_id'], 'integer'],
            [['user_id', 'template_id', 'element_id', 'last_updated'], 'safe'],
            [['value'], 'string'],
            [['value'], 'default', 'value' => ''], // Ensure a default empty string
        ];
    }

    public function getElement()
    {
        return $this->hasOne(Element::class, ['id' => 'element_id']);
    }

}
