<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class AdminOptions extends ActiveRecord
{
    public static function tableName()
    {
        return 'admin_options';
    }

    public static function getValue($name)
    {
        return static::find()->where(['name' => $name])->select('value')->scalar();
    }

    public static function setValue($name, $value)
    {
        $option = static::findOne(['name' => $name]);
        if (!$option) {
            $option = new static();
            $option->name = $name;
        }
        $option->value = (int) $value;
        return $option->save(false);
    }
}
