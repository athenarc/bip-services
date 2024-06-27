<?php

/*
 * Widget using customisation of Checkboxlists in order to work
 * with bootsnipp applying bootstrap classes on checkbox inputs
 *
 *
 */

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class CustomFiltersCheckboxList extends Widget
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

    public $id;


    public function init()
    {
        parent::init();
    }

    public function run()
    {
        /*
        *  multicheckboxes
        */
        $checkboxList = $this->form
            ->field($this->model, $this->name)
            ->checkboxList(
                $this->items,
                [
                    'unselect' => null,
                    'onchange' => '$(this).closest(\'form\').submit()',
                    'id' => $this->id,
                    /*
                    * In order to use bootsnipp that makes the checkbox have the "success" color, we need to change the way each checkbox is displayed.
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

        return $checkboxList;

    }
}

?>