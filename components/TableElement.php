<?php

namespace app\components;

use yii\base\Widget;


class TableElement extends Widget
{
    public $edit_perm;
    public $element_id;
    public $title;
    public $description;
    public $heading_type;
    public $hide_when_empty;
    public $max_rows;
    public $table_headers;
    public $table_data;
    public $last_updated;
    public $for_print;

    public function init()
    {
        parent::init();
    }

    public function run()
    {        
        if ($this->for_print) {
            return $this->render('pdf/table_element');
        }
        return $this->render('table_element');
    }


}

