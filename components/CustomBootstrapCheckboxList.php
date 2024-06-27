<?php

/*
 * Widget using customisation of Checkbox and Checkboxlists in order to work
 * with bootsnipp applying bootstrap classes on radio and checkbox inputs
 *
 * author: @Hlias
 */

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class CustomBootstrapCheckboxList extends Widget
{
    /*
     * The model on which the form is based
     */
    public $model;
    /*
     * The form element on which
     * the checkboxlist will appear
     */
    public $form;
    /*
     * The actual items array to be used
     */
    public $items;
    /*
     * The bootstrap class to use
     */
    public $item_class;
    /*
     * The name of the form element
     */
    public $name;

    /*
     * string, the value that should be submitted when none of the radio buttons is selected.
     * You may set this option to be null to prevent default value submission.
     * If this option is not set, an empty string will be submitted.
     */
    public $unselect;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if(isset($this->items))
        {
            /*
             * Do this for multicheckboxes
             */
            return $this->form->field($this->model, $this->name)
                ->checkboxList(
                    $this->items,
                    [
                        'unselect' => $this->unselect,
                        /*
                        * In order to use bootsnipped that makes the checkbox have the "success" color, we need to change the way each checkbox is displayed.
                        * The required format is '<div class="checkbox checkbox-custom checkbox-inline"><input><label></label></div>'
                        *
                        * To do this we set the 'item' parameter to a callback function for each item. This callback has all required checkbox fields
                        */
                        'item' => function ($index, $label, $name, $checked, $value)
                        {
                            $item_id = $name . '_' . $index;
                            $actually_checked = ($checked == '1') ? 'checked="checked"' : '';
                            return "<div class='" . $this->item_class . "'><input id='$item_id' name='$name' $actually_checked value='$value' type='checkbox' class='checkbox_filter'><label for='$item_id' >$label</label></div>";
                        }
                    ]
            );
        }
        /*
         * Do this for single element in login form
         */
        else
        {
            return $this->form->field($this->model, $this->name, ['options' => ['class' => $this->item_class . " col-lg-offset-1 col-lg-3 "]])->checkbox(['template' => '{input}{beginLabel}{labelTitle}{endLabel}{error}{hint}']/*['label' => "Remember Me", 'template' => '<div class="">{beginLabel}{labelTitle}{endLabel}{input}{error}{hint}</div>']*/);
        }
    }
}

?>