<?php

namespace app\models;

use yii\base\Model;

class ElementFacetsForm extends Model
{
    // public $type;
    // public $selected;
    // public $visualize_opt;
    // public $numbers_opt;
    // public $border_opt;
    public $selectedFacets = [];
    public $linked_contribution_element_id;

    public function rules()
    {
        return [
            ['selectedFacets', 'safe'],
            ['linked_contribution_element_id', 'integer']
            // [['type'], 'string'],
            // [['selected', 'visualize_opt, numbers_opt, border_opt'], 'boolean'],
            // [['selected', 'visualize_opt, numbers_opt, border_opt'], 'default', 'value'=> false],
        ];
    }
}