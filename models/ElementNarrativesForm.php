<?php

namespace app\models;

use yii\base\Model;

class ElementNarrativesForm extends Model
{
    public $title;
    public $heading_type;
    public $description;
    public $hide_when_empty;
    public $limit_value;
    public $limit_type;

    public function rules()
    {
        return [
            [['description'], 'string'],
            [['title'], 'string', 'max' => 1024],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['hide_when_empty'], 'boolean'],
            [['hide_when_empty'], 'default', 'value'=> false],
            [['limit_value'], 'integer', 'min' => 0],
            [['limit_type'], 'in', 'range' => [ElementNarratives::TYPE_WORDS, ElementNarratives::TYPE_CHARACTERS]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'heading_type' => 'Header size',
        ];
    }
}