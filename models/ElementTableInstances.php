<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%element_table_instances}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $template_id
 * @property int $element_id
 * @property string|null $table_data
 * @property string|null $last_updated
 *
 * @property Elements $element
 */
class ElementTableInstances extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_table_instances}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'template_id', 'element_id'], 'required'],
            [['user_id', 'template_id', 'element_id'], 'integer'],
            [['table_data'], 'string'],
            [['last_updated'], 'safe'],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],
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
            'table_data' => 'Table Data',
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
     * Gets query for [[Element]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElement()
    {
        return $this->hasOne(Elements::class, ['id' => 'element_id']);
    }
}
