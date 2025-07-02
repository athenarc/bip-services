<?php

namespace app\models;

use yii\base\Model;

class ElementIndicatorsForm extends Model
{
    public $selectedIndicators = [];
    public $semanticsOrder = '';
    public $indicatorOrder = '';
    public $linked_contribution_element_id;

    public function rules()
    {
        return [
            ['selectedIndicators', 'safe'],
            ['semanticsOrder', 'string'], 
            ['indicatorOrder', 'string'], 
            ['linked_contribution_element_id', 'integer']
        ];
    }
}