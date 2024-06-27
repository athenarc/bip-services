<?php
/*
 * Widget using customisation of RadioList in order to work
 * with bootsnipp applying bootstrap classes on radio and checkbox inputs
 *
 * author: @Hlias
 */

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;


class CustomBootstrapRadioList extends Widget
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
        return $this->form->field($this->model, $this->name)->radioList($this->items, ['unselect' => $this->unselect,
            /*
             * In order to use bootsnipped that makes the checkbox have the "success" color, we need to change the way each checkbox is displayed.
             * The required format is '<div class="checkbox checkbox-custom checkbox-inline"><input><label></label></div>'
             *
             * To do this we set the 'item' parameter to a callback function for each item. This callback has all required checkbox fields
             */
            'item' => function ($index, $label, $name, $checked, $value)
             {
                $id = $name . '_' . $index;
                $actually_checked = ($checked == '1') ? 'checked="checked"' : '';
                return "<div class='radio radio-custom radio-inline'><input id='$id' name='$name' $actually_checked value='$value' type='radio'><label for='$id'>$label</label></div>";
             }]);
    }

}
