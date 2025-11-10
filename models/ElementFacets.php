<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "element_facets".
 *
 * @property int $facet_id
 * @property int $element_id
 * @property string|null $margin_top
 * @property string|null $margin_right
 * @property string|null $margin_bottom
 * @property string|null $margin_left
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
            [['margin_top', 'margin_right', 'margin_bottom', 'margin_left'], 'string', 'max' => 50],
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

    public static function getConfigFacet($element_id) {
        $config = [];
    
        $facets_config = self::find()
            ->where(['element_id' => $element_id])
            ->with('facet')
            ->all();
    
        foreach ($facets_config as $f_config) {
            $type = $f_config->facet->type;
            $facet_data = $f_config->facet->toArray();
            $facet_data['linked_contribution_element_id'] = $f_config->linked_contribution_element_id;
            // Add margin data to the first facet entry only
            if (!isset($config['_margins'])) {
                $facet_data['margin_top'] = $f_config->margin_top;
                $facet_data['margin_right'] = $f_config->margin_right;
                $facet_data['margin_bottom'] = $f_config->margin_bottom;
                $facet_data['margin_left'] = $f_config->margin_left;
                $config['_margins'] = [
                    'margin_top' => $f_config->margin_top,
                    'margin_right' => $f_config->margin_right,
                    'margin_bottom' => $f_config->margin_bottom,
                    'margin_left' => $f_config->margin_left,
                ];
            }
            $config[$type] = $facet_data;
        }  
        return $config;
    } 

    public static function getLinkedContributionElementId($element_id)
    {
        return self::find()
            ->select('linked_contribution_element_id')
            ->where(['element_id' => $element_id])
            ->scalar();
    }
}
