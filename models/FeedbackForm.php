<?php

namespace app\models;

use Yii;
use yii\base\Model;

class FeedbackForm extends Model
{
    public $email;
    public $title;
    public $description;
    public $category;

    public function rules()
    {
        return [
            [['title', 'description', 'category'], 'required'],
            ['category', 'in', 'range' => ['general inquiry', 'bug or problem', 'new feature proposal', 'suggestion', 'user account issue']],
            ['title', 'string', 'max' => 255],
            ['description', 'string', 'max' => 800],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'Subject',
            'description' => 'How can we help?',
            'category' => 'Category',
        ];
    }
}