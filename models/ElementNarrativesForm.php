<?php

namespace app\models;

use yii\base\Model;

class ElementNarrativesForm extends Model
{
    public $title;
    public $description;
    public $hide_when_empty;

    public function rules()
    {
        return [
            [['description'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['hide_when_empty'], 'boolean'],
            [['hide_when_empty'], 'default', 'value'=> false],
        ];
    }
}