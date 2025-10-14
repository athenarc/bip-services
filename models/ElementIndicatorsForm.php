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

    public function validateRequired()
    {
        if (empty($this->linked_contribution_element_id)) {
            $this->addError('linked_contribution_element_id', 'Please select a Contributions List to link.');
            return false;
        }
        return true;
    }
}