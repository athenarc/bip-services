<?php

namespace app\models;

use yii\base\Model;

class ProtocolIndicatorsForm extends Model
{
    public $selectedIndicators = [];

    public function rules()
    {
        return [
            ['selectedIndicators', 'safe'],
        ];
    }
}