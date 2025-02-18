<?php

namespace app\models;

use yii\base\Model;

class ElementDividersForm extends Model
{
    public $title;
    public $heading_type;
    public $top_padding;
    public $bottom_padding;
    public $show_top_hr;
    public $show_bottom_hr;

    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 1024],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['top_padding', 'bottom_padding'], 'string', 'max' => 5],
            [['show_top_hr', 'show_bottom_hr'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'Title',
            'heading_type' => 'Header size',
            'top_padding' => 'Top',
            'bottom_padding' => 'Bottom',
            'show_top_hr' => 'Show top rule',
            'show_bottom_hr' => 'Show bottom rule',
        ];
    }
}