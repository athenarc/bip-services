<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%element_dropdown_options}}".
 *
 * @property int $id
 * @property int $element_dropdown_id
 * @property string $option_name
 *
 * @property ElementDropdown $elementDropdown
 * @property ElementDropdownInstances[] $elementDropdownInstances
 */
class ElementDropdownOptions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_dropdown_options}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // the element_dropdown_id is assigned from ElementDropdown model, so no need for validation
            // [['element_dropdown_id'], 'integer'],
            // [['element_dropdown_id'], 'required'],
            // [['element_dropdown_id'], 'exist', 'skipOnError' => true, 'targetClass' => ElementDropdown::class, 'targetAttribute' => ['element_dropdown_id' => 'id']],
            // [['option_name'], 'required'],
            [['option_name'], 'string', 'max' => 255],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'element_dropdown_id' => 'Element Dropdown ID',
            'option_name' => 'Option Name',
        ];
    }

    /**
     * Gets query for [[ElementDropdown]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementDropdown()
    {
        return $this->hasOne(ElementDropdown::class, ['id' => 'element_dropdown_id']);
    }

    /**
     * Gets query for [[ElementDropdownInstances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementDropdownInstances()
    {
        return $this->hasMany(ElementDropdownInstances::class, ['option_id' => 'id']);
    }
}
