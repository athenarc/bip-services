<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "facets".
 *
 * @property int $id
 * @property string|null $type
 * @property int|null $visualize_opt
 * @property int|null $numbers_opt
 * @property int|null $border_opt
 *
 * @property ElementFacets[] $elementFacets
 * @property Elements[] $elements
 */
class Facets extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'facets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'string'],
            [['selected', 'visualize_opt', 'numbers_opt', 'border_opt'], 'boolean'],
            [['selected', 'visualize_opt', 'numbers_opt', 'border_opt'], 'default', 'value'=> false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'selected' => 'Selected',
            'visualize_opt' => 'Visualize Option',
            'numbers_opt' => 'Numbers Option',
            'border_opt' => 'Border Option',
        ];
    }

    /**
     * Gets query for [[ElementFacets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementFacets()
    {
        return $this->hasMany(ElementFacets::class, ['facet_id' => 'id']);
    }

    /**
     * Gets query for [[Elements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElements()
    {
        return $this->hasMany(Elements::class, ['id' => 'element_id'])->viaTable('element_facets', ['facet_id' => 'id']);
    }
}
