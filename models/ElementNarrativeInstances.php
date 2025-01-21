<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


class ElementNarrativeInstances extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'element_narrative_instances';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'element_id', 'template_id'], 'required'],
            
            [['user_id'], 'integer'],
            [['template_id'], 'integer'],
            [['element_id'], 'integer'],
            [['value'], 'string'],
        ];
    }
    
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['last_updated'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['last_updated'],
                ],
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }
}