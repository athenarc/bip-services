<?php

/*
 * Define the namespace of the widget
 */
namespace app\components;

/*
 * Includes
 */
use yii\base\Widget;

/*
 * The widget class
 */
class ScholarSidebar extends Widget
{
    /*
     * Widget properties
     */
    public $folders;
    public $highlight_key;

    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
    }

    /*
     * Running the widget
     */
    public function run()
    {
        return $this->render('scholar_sidebar', [
            'folders' => $this->folders,
            'highlight_key' => $this->highlight_key,
        ]);
    }

}

?>