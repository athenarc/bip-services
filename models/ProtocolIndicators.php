<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "protocol_indicators".
 *
 * @property int $indicator_id
 * @property int $protocol_id
 *
 * @property Indicators $indicator
 * @property AssessmentProtocols $protocol
 */
class ProtocolIndicators extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'protocol_indicators';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['indicator_id', 'protocol_id'], 'required'],
            [['indicator_id', 'protocol_id'], 'integer'],
            [['indicator_id', 'protocol_id'], 'unique', 'targetAttribute' => ['indicator_id', 'protocol_id']],
            [['protocol_id'], 'exist', 'skipOnError' => true, 'targetClass' => AssessmentProtocols::class, 'targetAttribute' => ['protocol_id' => 'id']],
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
            'protocol_id' => 'Protocol ID',
        ];
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

    /**
     * Gets query for [[Protocol]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProtocol()
    {
        return $this->hasOne(AssessmentProtocols::class, ['id' => 'protocol_id']);
    }
}
