<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "element_narratives".
 *
 * @property int $id
 * @property int $element_id
 * @property string $title
 * @property string|null $description
 * @property boolean|null $hide_when_empty
 *
 * @property Elements $element
 */
class ElementNarratives extends \yii\db\ActiveRecord
{

    public $value;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'element_narratives';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['element_id'], 'required'],
            [['element_id'], 'integer'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['hide_when_empty'], 'boolean'],
            [['hide_when_empty'], 'default', 'value'=> false],
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
            'element_id' => 'Element ID',
            'title' => 'Title',
            'description' => 'Description',
            'hide_when_empty' => 'Hide when Empty',
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

    public function getConfigNarrative($element_id, $template_id, $user_id) {

        $element_config = ElementNarratives::find()->where([ 'element_id' => $element_id ])->one();

        // get info for narrative instances
        // TODO: fetch with one query: outer join
        $element_intance_config = ElementNarrativeInstances::find()
        ->where([
            'element_id' => $element_id,
            'user_id' => $user_id,
            'template_id' => $template_id,
        ])
        ->one();

        if ($element_intance_config) {
            $element_config->value = $element_intance_config->value;
        }

        return $element_config;
    }
}
