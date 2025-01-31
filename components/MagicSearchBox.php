<?php

/*
 * Widget for displaying a magic search box with the following properties:
 * i) A user types in a search keyword, denoting a specific entity (something 
 * found in a db table, such as a miRNA name, TF, gene, etc)
 * ii) The search box automatically suggests terms, while the user is writing
 * iii) After selecting one of the suggestions, the selection is displayed under
 *      the box. Additional selections may be made afterwards.
 * 
 * @params:
 * min_char_to_start: should be a number that sets the character threshold, after
 *                    which the form starts displaying suggestions
 * expansion: a constant (LEFT, RIGHT, BOTH), that determines on which side an
 *            SQL "like" query will put the wildcard character
 * suggestions_num: a number determining how many suggestions should be displayed
 *                  at a time
 * html_params[]: an  associative array for setting parameters such as html id etc.
 * ajax_action: the name of the controller/action responsible for getting suggested
 *              results
 * 
 * @author: Ilias Kanellos (First version: September 2015)
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
class MagicSearchBox extends Widget
{
    /*
     * Widget properties
     */
    public $submit_action;
    public $ajax_action;
    public $suggestions_num;
    public $expansion;
    public $min_char_to_start;
    public $html_params = array();
    
    /*
     * Elements that have previously been selected by this input
     * Can be either set as widget parameter, or from a GET request
     */
    public $selected_elements;
    
    
    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();

        // if selected_elements has not been set as a widget parameter, make the GET request to set it
        if (!isset($this->selected_elements)){

            $this->selected_elements = array();
            /*
            * Set the selected elements array in order to print 
            * hidden inputs containing all these elements
            */
            if(array_key_exists($this->html_params["name"], $_GET))
            {
                //Remove empty elements!
                $this->selected_elements = array_unique(array_filter($_GET[$this->html_params["name"]]));
            }
            //TODO: force an automatic name of sorts, or return an error
            else
            {
                ;
            }
        }
    }
    
    /*
     * Running the widget a.k.a. rendering results
     */
    public function run()
    {
        return $this->render('magicsearchbox');
    }

}

?>