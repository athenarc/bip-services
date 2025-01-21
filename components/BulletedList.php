<?php

namespace app\components;

use yii\base\Widget;


class BulletedList extends Widget
{

    public $element_id;
    public $title;
    public $heading_type;
    public $description;
    public $elements_number;
    public $edit_perm;
    public $items;
    public $last_updated;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $this->last_updated = $this->getLastUpdate();

        return $this->render('bulleted_list');
    }

    // every $item in the $items array has a last_update value
    // here, we find the lastest of them
    private function getLastUpdate() {
        $latestUpdated = array_reduce($this->items, function ($latest, $item) {
            $currentTimestamp = strtotime($item['last_updated']);
            $latestTimestamp = $latest ? strtotime($latest['last_updated']) : 0;
        
            return $currentTimestamp > $latestTimestamp ? $item : $latest;
        });
        
        return ($latestUpdated) ? $latestUpdated['last_updated'] : null;
    }

}

