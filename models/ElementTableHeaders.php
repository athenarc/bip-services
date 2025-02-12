<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%element_table_headers}}".
 *
 * @property int $id
 * @property int $element_table_id
 * @property string $header_name
 * @property int $header_width
 *
 * @property ElementTable $elementTable
 * @property ElementTableInstances[] $elementTableInstances
 */
class ElementTableHeaders extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_table_headers}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // the element_table_id is assigned from ElementTable model, so no need for validation
            // [['element_table_id'], 'integer'],
            // [['element_table_id'], 'required'],
            // [['element_table_id'], 'exist', 'skipOnError' => true, 'targetClass' => ElementTable::class, 'targetAttribute' => ['element_table_id' => 'id']],
            // [['header_name'], 'required'],
            [['header_name'], 'string', 'max' => 255],
            [['header_width'], 'integer', 'min' => 1, 'max' => 100, 'message' => 'Header width must be an integer between 1 and 100.']


        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'element_table_id' => 'Element Table ID',
            'header_name' => 'Header Name',
            'header_width' => 'Header Width Percentage',
        ];
    }

    /**
     * Gets query for [[ElementTable]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementTable()
    {
        return $this->hasOne(ElementTable::class, ['id' => 'element_table_id']);
    }

}
