<?php

namespace app\models;

use yii\base\Model;

class ElementIndicatorsForm extends Model
{
    public $selectedIndicators = [];
    public $semanticsOrder = '';
    public $indicatorOrder = '';

    public function rules()
    {
        return [
            ['selectedIndicators', 'safe'],
            ['semanticsOrder', 'string'], 
            ['indicatorOrder', 'string'], 
        ];
    }
}