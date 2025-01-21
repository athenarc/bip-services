<?php

namespace app\components;

use yii\base\Widget;


class DropdownElement extends Widget
{

    public $index;
    public $edit_perm;
    public $element_id;
    public $title;
    public $heading_type;
    public $description;
    public $hide_when_empty;
    public $elementDropdownOptionsArray;
    public $option_id;
    public $last_updated;

    public function init()
    {
        parent::init();
    }


    public function run()
    {
        return $this->render('dropdown_element');
    }

}
