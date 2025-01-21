<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%element_contributions}}".
 *
 * @property int $id
 * @property int $element_id
 * @property string $heading_type
 * @property int|null $show_header
 * @property int|null $show_pagination
 * @property string|null $sort
 * @property int|null $top_k
 * @property int|null $page_size
 *
 * @property Elements $element
 */
class ElementContributions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_contributions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['element_id'], 'required'],
            [['element_id'], 'integer'],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],
            [['show_header', 'show_pagination'], 'boolean'],
            [['sort'], 'in', 'range' => array_keys(Yii::$app->params['impact_fields'])],
            [['top_k', 'page_size'], 'integer', 'min' => 1, 'message' => 'Please enter a positive integer.'],
            [['top_k', 'page_size'], 'default', 'value' => null],
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
            'show_header' => 'Show header',
            'show_pagination' => 'Show pagination',
            'sort' => 'Sort',
            'top_k' => 'Top K',
            'page_size' => 'Page size',
            'heading_type' => 'Header size',
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
     * Retrieves the configuration for a contribution list based on element_id.
     *
     * @param int $element_id
     * @return ElementContributions|null
     */
    public function getConfigContributions($element_id) {
        return self::find()->where([ 'element_id' => $element_id ])->one();
    }
}
