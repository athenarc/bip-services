<?php

namespace app\components;

use yii\base\Widget;


class NarrativeElement extends Widget
{

    public $index;
    public $element_id;
    public $title;
    public $heading_type;
    public $description; 
    public $hide_when_empty;
    public $value;
    public $edit_perm;
    public $limit_type;
    public $limit_value;
    public $messages;
    public $last_updated;

    public function init()
    {
        parent::init();
    }


    public function run()
    {
        return $this->render('narrative_element');
    }

}

