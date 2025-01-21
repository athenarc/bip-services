<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "element_indicators".
 *
 * @property int $indicator_id
 * @property int $element_id
 *  @property string $heading_type
 * @property string|null $status 
 * @property int|null $semantics_order
 * @property int|null $indicator_order
 *
 * @property Elements $element
 * @property Indicators $indicator
 */
class ElementIndicators extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'element_indicators';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['indicator_id', 'element_id'], 'required'],
            [['indicator_id', 'element_id', 'semantics_order', 'indicator_order'], 'integer'],
            [['status'], 'string'], 
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['indicator_id', 'element_id'], 'unique', 'targetAttribute' => ['indicator_id', 'element_id']],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],
            [['indicator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Indicators::class, 'targetAttribute' => ['indicator_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'indicator_id' => 'Indicator ID',
            'element_id' => 'Element ID',
            'status' => 'Status', 
            'semantics_order' => 'Semantics Order',
            'indicator_order' => 'Indicator Order'
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
     * Gets query for [[Indicator]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicator()
    {
        return $this->hasOne(Indicators::class, ['id' => 'indicator_id']);
    }

    public function getConfigIndicator($element_id) {
        $config = [];

        $indicators_config = ElementIndicators::find()->where([ 'element_id' => $element_id ])->with('indicator')->all();
        foreach ($indicators_config as $i_config) {

            $config[$i_config->indicator->name] = [
                'indicator' => $i_config->indicator,
                'status' => $i_config->status,
                'semantics_order' => $i_config->semantics_order,
                'indicator_order' => $i_config->indicator_order
            ];
        }

        return $config;
    }
}
