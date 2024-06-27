<?php

namespace app\models;

use yii\db\ActiveRecord;

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
}