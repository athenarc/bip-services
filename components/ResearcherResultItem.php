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
class ResearcherResultItem extends Widget
{
    /*
     * Widget properties
     */

    public $id;
    public $orcid;
    public $name;

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
        return $this->render('researcher_result_item');
    }

}

?>