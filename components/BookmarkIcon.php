<?php

/*
 * Widget for displaying bookmark icon
 *
 *
 *
 *
 * (First version: Aug 2021)
 */

# ---------------------------------------------------------------------------- #

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
class BookmarkIcon extends Widget
{
    /*
     * Widget properties
     */
    public $user_liked;
    public $user_logged;
    public $id_bookmark;


    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
    }

    /*
     * Running the widget a.k.a. rendering results
     */
    public function run()
    {

        return $this->render('bookmark_icon', [
            'user_liked' => $this->user_liked,
            'user_logged' => $this->user_logged,
            'id_bookmark' => $this->id_bookmark
        ]);
    }
}

?>