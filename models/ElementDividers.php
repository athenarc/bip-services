<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "element_section_divider".
 *
 * @property int $id
 * @property int $element_id
 * @property string $title
 * @property string $heading_type
 * @property string $top_padding
 * @property string $bottom_padding
 * @property bool $show_top_hr
 * @property bool $show_bottom_hr
 *
 * @property Elements $element
 */
class ElementDividers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'element_section_divider';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 255],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['top_padding', 'bottom_padding'], 'string', 'max' => 50],
            [['element_id'], 'required'],
            [['element_id'], 'integer'],
            [['show_top_hr', 'show_bottom_hr'], 'boolean'],
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
            'heading_type' => 'Header size',
            'top_padding' => 'Top padding (px)',
            'bottom_padding' => 'Bottom padding (px)',
            'show_top_hr' => 'Show top rule',
            'show_bottom_hr' => 'Show bottom rule',
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
     * Retrieves the configuration for a divider based on element_id, template_id, and user_id.
     *
     * @param int $element_id
     * @return ElementDividers|null
     */
    public function getConfigDivider($element_id) {
        return ElementDividers::find()->where([ 'element_id' => $element_id ])->one();
    }
}
