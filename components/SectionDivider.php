<?php

namespace app\components;

use yii\base\Widget;


class SectionDivider extends Widget
{

    public $index;
    public $element_id;
    public $title;
    public $heading_type;
    public $description;
    public $show_description_tooltip;
    public $top_padding;
    public $bottom_padding;
    public $show_top_hr;
    public $show_bottom_hr;
    public $edit_perm;

    public function init()
    {
        parent::init();
    }


    public function run()
    {
        return $this->render('section_divider');
    }

}

