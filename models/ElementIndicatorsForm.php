<?php

namespace app\models;

use yii\base\Model;

class ElementIndicatorsForm extends Model {
    public $selectedIndicators = [];

    public $semanticsOrder = '';

    public $indicatorOrder = '';

    public $linked_contribution_element_id;

    public function rules() {
        return [
            ['selectedIndicators', 'safe'],
            ['semanticsOrder', 'string'],
            ['indicatorOrder', 'string'],
            ['linked_contribution_element_id', 'required', 
                'message' => 'Please select a Contributions List to link.',
                'whenClient' => "function (attribute, value) {
                    return $('#elements-type').val() === 'Indicators';
                }"
            ],
            ['linked_contribution_element_id', 'integer']
        ];
    }

}
