<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "indicators".
 *
 * @property int $id
 * @property string $name
 * @property string|null $level
 * @property string|null $semantics
 * @property string|null $intuition
 * @property string|null $parameters
 * @property string|null $calculation
 * @property string|null $limitations
 * @property string|null $availability
 * @property string|null $code
 * @property string|null $references
 *
 * @property ProtocolIndicators[] $protocolIndicators
 * @property AssessmentProtocols[] $protocols
 */
class Indicators extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'indicators';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['level', 'semantics', 'intuition', 'parameters', 'calculation', 'limitations', 'availability', 'code', 'references'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'level' => 'Level',
            'semantics' => 'Semantics',
            'intuition' => 'Intuition',
            'parameters' => 'Parameters',
            'calculation' => 'Calculation',
            'limitations' => 'Limitations',
            'availability' => 'Availability',
            'code' => 'Code',
            'references' => 'References',
        ];
    }

    /**
     * Gets query for [[ProtocolIndicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProtocolIndicators()
    {
        return $this->hasMany(ProtocolIndicators::class, ['indicator_id' => 'id']);
    }

    /**
     * Gets query for [[Protocols]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProtocols()
    {
        return $this->hasMany(AssessmentProtocols::class, ['id' => 'protocol_id'])->viaTable('protocol_indicators', ['indicator_id' => 'id']);
    }

    public static function getImpactIndicatorsAsArray($level)
    {
        if (isset($level) && is_string($level)) {
            $indicators = self::find()
                ->where(['level' => $level, 'semantics' => 'Impact'])
                ->select(['name', 'intuition'])
                ->asArray()
                ->all();
            
            if (is_array($indicators)) {
                $impact_indicators = ArrayHelper::map($indicators, 'name', 'intuition');
                return $impact_indicators;
            }
        }

        return [];
    }
}
