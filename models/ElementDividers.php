<?php

namespace app\models;

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
 * @property string|null $margin_top
 * @property string|null $margin_right
 * @property string|null $margin_bottom
 * @property string|null $margin_left
 *
 * @property Elements $element
 */
class ElementDividers extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'element_section_divider';
    }

    public function rules() {
        return [
            [['title'], 'string', 'max' => 1024],
            [['description'], 'string'],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['top_padding', 'bottom_padding'], 'string', 'max' => 50],
            [['element_id'], 'required'],
            [['element_id'], 'integer'],
            [['show_top_hr', 'show_bottom_hr', 'show_description_tooltip'], 'boolean'],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],
            [['margin_top', 'margin_right', 'margin_bottom', 'margin_left'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'element_id' => 'Element ID',
            'title' => 'Title',
            'description' => 'Description',
            'heading_type' => 'Header size',
            'top_padding' => 'Top padding (px)',
            'bottom_padding' => 'Bottom padding (px)',
            'show_top_hr' => 'Show top rule',
            'show_bottom_hr' => 'Show bottom rule',
            'show_description_tooltip' => 'Show description as a tooltip',
            'margin_top' => 'Top margin',
            'margin_right' => 'Right margin',
            'margin_bottom' => 'Bottom margin',
            'margin_left' => 'Left margin',
        ];
    }

    /**
     * Gets query for [[Element]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElement() {
        return $this->hasOne(Elements::class, ['id' => 'element_id']);
    }

    public function beforeSave($insert) {
        // Add 'px' unit to margin values if they're just numbers
        foreach (['margin_top', 'margin_right', 'margin_bottom', 'margin_left'] as $marginAttr) {
            if (! empty($this->$marginAttr) && is_numeric($this->$marginAttr)) {
                $this->$marginAttr = $this->$marginAttr . 'px';
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * Retrieves the configuration for a divider based on element_id, template_id, and user_id.
     *
     * @param int $element_id
     * @return ElementDividers|null
     */
    public function getConfigDivider($element_id) {
        return self::find()->where(['element_id' => $element_id])->one();
    }
}
