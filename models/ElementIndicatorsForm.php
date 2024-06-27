<?php

namespace app\models;

use yii\base\Model;

class ElementIndicatorsForm extends Model
{
    public $selectedIndicators = [];

    public function rules()
    {
        return [
            ['selectedIndicators', 'safe'],
        ];
    }
}