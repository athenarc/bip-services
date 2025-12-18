<?php

namespace app\models;

use yii\base\Model;

class ElementDividersForm extends Model {
    public $title;

    public $heading_type;

    public $description;

    public $show_description_tooltip;

    public $top_padding;

    public $bottom_padding;

    public $show_top_hr;

    public $show_bottom_hr;

    public $margin_top;

    public $margin_right;

    public $margin_bottom;

    public $margin_left;

    public function rules() {
        return [
            [['title'], 'string', 'max' => 1024],
            [['description'], 'string'],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['top_padding', 'bottom_padding'], 'string', 'max' => 5],
            [['show_top_hr', 'show_bottom_hr', 'show_description_tooltip'], 'boolean'],
            [['margin_top', 'margin_right', 'margin_bottom', 'margin_left'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels() {
        return [
            'title' => 'Title',
            'description' => 'Description',
            'heading_type' => 'Header size',
            'top_padding' => 'Top',
            'bottom_padding' => 'Bottom',
            'show_top_hr' => 'Show top rule',
            'show_bottom_hr' => 'Show bottom rule',
            'show_description_tooltip' => 'Show description as tooltip',
            'margin_top' => 'Top margin',
            'margin_right' => 'Right margin',
            'margin_bottom' => 'Bottom margin',
            'margin_left' => 'Left margin',
        ];
    }
}
