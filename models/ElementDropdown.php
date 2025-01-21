<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%element_dropdown}}".
 *
 * @property int $id
 * @property int $element_id
 * @property string $title
 * @property string|null $description
 * @property boolean|null $hide_when_empty
 *
 *
 * @property Elements $element
 * @property ElementDropdownOptions[] $elementDropdownOptions
 */
class ElementDropdown extends \yii\db\ActiveRecord
{

    public $option_id;    // Exists in ElementDropdownInstances, needed for getConfigDropdown
    public $last_updated; // Exists in ElementDropdownInstances, needed for getConfigDropdown
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_dropdown}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['element_id'], 'required'],
            [['element_id'], 'integer'],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['hide_when_empty'], 'boolean'],
            [['hide_when_empty'], 'default', 'value'=> false],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'element_id' => 'Element ID',
            'title' => 'Title',
            'description' => 'Description',
            'hide_when_empty' => 'Hide when Empty',
            'heading_type' => 'Header Size',
        ];
    }

    /**
     * Gets query for [[Element]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElement()
    {
        return $this->hasOne(Elements::class, ['id' => 'element_id']);
    }

    /**
     * Gets query for [[ElementDropdownOptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementDropdownOptions()
    {
        return $this->hasMany(ElementDropdownOptions::class, ['element_dropdown_id' => 'id']);
    }

    public function getConfigDropdown($element_id, $template_id, $user_id) {

        // eager loading
        $element_config = self::find()->with('elementDropdownOptions')->where([ 'element_id' => $element_id ])->one();

        // get info for dropdown instances
        // TODO: fetch with one query: outer join
        $element_instance_config = ElementDropdownInstances::find()
        ->where([
            'element_id' => $element_id,
            'user_id' => $user_id,
            'template_id' => $template_id,
        ])
        ->one();

        // print_r($element_config);
        // print_r($element_instance_config);
        // exit;

        if ($element_instance_config) {
            $element_config->option_id = $element_instance_config->option_id;
            $element_config->last_updated = $element_instance_config->last_updated;
        }

        return $element_config;
    }
}
