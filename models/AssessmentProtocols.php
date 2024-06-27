<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assessment_protocols".
 *
 * @property int $id
 * @property int $assessment_framework_id
 * @property string $name
 * @property string|null $scope
 *
 * @property AssessmentFrameworks $assessmentFramework
 * @property Indicators[] $indicators
 * @property ProtocolIndicators[] $protocolIndicators
 */
class AssessmentProtocols extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assessment_protocols';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['assessment_framework_id', 'name'], 'required'],
            [['assessment_framework_id'], 'integer'],
            [['scope'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['assessment_framework_id'], 'exist', 'skipOnError' => true, 'targetClass' => AssessmentFrameworks::class, 'targetAttribute' => ['assessment_framework_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'assessment_framework_id' => 'Assessment Framework ID',
            'name' => 'Name',
            'scope' => 'Scope',
        ];
    }

    /**
     * Gets query for [[AssessmentFramework]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessmentFramework()
    {
        return $this->hasOne(AssessmentFrameworks::class, ['id' => 'assessment_framework_id']);
    }

    /**
     * Gets query for [[Indicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicators()
    {
        return $this->hasMany(Indicators::class, ['id' => 'indicator_id'])->viaTable('protocol_indicators', ['protocol_id' => 'id']);
    }

    /**
     * Gets query for [[ProtocolIndicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProtocolIndicators()
    {
        return $this->hasMany(ProtocolIndicators::class, ['protocol_id' => 'id']);
    }
}
