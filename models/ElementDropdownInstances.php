<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%element_dropdown_instances}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $template_id
 * @property int $element_id
 * @property int $option_id
 * @property string|null $last_updated
 *
 * @property ElementDropdownOptions $option
 */
class ElementDropdownInstances extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_dropdown_instances}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'template_id', 'element_id', 'option_id'], 'required'],
            [['user_id', 'template_id', 'element_id', 'option_id'], 'integer'],
            [['last_updated'], 'safe'],
            [['option_id'], 'exist', 'skipOnError' => true, 'targetClass' => ElementDropdownOptions::class, 'targetAttribute' => ['option_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'template_id' => 'Template ID',
            'element_id' => 'Element ID',
            'option_id' => 'Option ID',
            'last_updated' => 'Last Updated',
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

    /**
     * Gets query for [[Option]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOption()
    {
        return $this->hasOne(ElementDropdownOptions::class, ['id' => 'option_id']);
    }
}
