<?php
namespace app\models;

use Yii;
use yii\base\Model;

/**
 * SurveyForm is the model behind the survey form
 *
 */
class SurveyForm extends Model
{
    public $comments;
    public $papers;
    public $checked;
    public $session_id;
    public $ordering;
    public $keywords;
    public $start_time;
    public $end_time;
    public $step;
    public $category;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            ['papers', 'string'], 
            ['checked', 'string'],
            ['comments', 'string'], 
            ['session_id', 'string'], 
            ['ordering', 'string'], 
            ['keywords', 'string'], 
            ['start_time', 'string'],
            ['end_time', 'string'],
            ['category', 'string'],
            ['step', 'integer'],
            [['keywords', 'ordering', 'session_id', 'category'], 'required']
        ];
    }

    public function store(){
        $command = Yii::$app->db->createCommand();
        $command->insert('survey', array(
            'session_id' => $this->session_id,
            'keywords' => $this->keywords,
            'ordering' => $this->ordering, 
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'step' => $this->step,
            'category' => $this->category,
            'papers' => $this->papers,
            'checked' => $this->checked, 
            'comments' => $this->comments
        ))->execute();
    }

    public static function check_session_id($session_id){
        return (new \yii\db\Query())
            ->select('session_id')
            ->from('survey')
            ->where(['session_id' => $session_id])
            ->exists();
    }
}


