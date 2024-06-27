<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assessment_frameworks".
 *
 * @property int $id
 * @property string $name
 * @property string|null $webpage
 * @property string|null $description
 *
 * @property AssessmentProtocols[] $assessmentProtocols
 */
class AssessmentFrameworks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assessment_frameworks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name', 'webpage'], 'string', 'max' => 255],
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
            'webpage' => 'Webpage',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[AssessmentProtocols]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessmentProtocols()
    {
        return $this->hasMany(AssessmentProtocols::class, ['assessment_framework_id' => 'id']);
    }
}
