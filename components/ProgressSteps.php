<?php

/*
 * Widget for displaying progress steps (see survey_step view) with the following properties:
 * 
 * @params:
 * col_class: the bootstrap column class to be used for each step
 * steps[]: an array with each item containing step metadata as: title, message
 * active: an integer indicating the active step index
 * 
 * @author: Serafeim Chatzopoulos (First version: January 2018)
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
use yii\helpers\Html;

/*
 * The widget class
 */
class ProgressSteps extends Widget
{
    /*
     * Widget properties
     */
    public $col_class;
    public $steps;
    public $active;

    
    
    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
    }

    // given the active index, this function sets the appropriate
    // css classes to all steps. Available css classes are: ['completed', 'active', 'disabled']
    private function fillStepCssStatus(){
        foreach($this->steps as $index => $data){
            if($this->active < $index)
              $this->steps[$index]['status'] = 'disabled'; 
            else if ($this->active > $index)
              $this->steps[$index]['status'] = 'complete';
            else 
              $this->steps[$index]['status'] = 'active';
        }
    }
    /*
     * Running the widget a.k.a. rendering results
     */
    public function run()
    {
        
        $this->fillStepCssStatus();
        echo Html::cssFile('@web/css/components/progress-steps.css');
        return $this->render('progress_steps');
    }

}

?>