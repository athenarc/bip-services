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
            [['email', 'title', 'description', 'category'], 'required'],
            ['email', 'email'],
            ['category', 'in', 'range' => ['bug', 'new feature proposal', 'suggestion', 'user account issue']],
            ['title', 'string', 'max' => 255],
            ['description', 'string', 'max' => 800],
        ];
    }
}
