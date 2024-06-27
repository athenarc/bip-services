<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "element_facets".
 *
 * @property int $facet_id
 * @property int $element_id
 *
 * @property Elements $element
 * @property Facets $facet
 */
class ElementFacets extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'element_facets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['facet_id', 'element_id'], 'required'],
            [['facet_id', 'element_id'], 'integer'],
            [['facet_id', 'element_id'], 'unique', 'targetAttribute' => ['facet_id', 'element_id']],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],
            [['facet_id'], 'exist', 'skipOnError' => true, 'targetClass' => Facets::class, 'targetAttribute' => ['facet_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'facet_id' => 'Facet ID',
            'element_id' => 'Element ID',
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
     * Gets query for [[Facet]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacet()
    {
        return $this->hasOne(Facets::class, ['id' => 'facet_id']);
    }

    public function getConfigFacet($element_id) {
        $config = [];

        $facets_config = ElementFacets::find()->where([ 'element_id' => $element_id ])->with('facet')->all();
        foreach ($facets_config as $f_config) {
            $config[$f_config->facet->type] = $f_config->facet;
        }
        return $config;
    }
}
