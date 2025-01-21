<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "elements".
 *
 * @property int $id
 * @property int $template_id
 * @property string $name
 * @property string|null $type
 * @property int|null $order
 *
 * @property Templates $template
 * @property Indicators[] $indicators
 */
class Elements extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'elements';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['template_id', 'name', 'type'], 'required'],
            [['template_id', 'order'], 'integer'],
            [['type'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => Templates::class, 'targetAttribute' => ['template_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => 'Template ID',
            'name' => 'Name',
            'type' => 'Type',
            'order' => 'Order',
        ];
    }

    /**
     * Gets query for [[Template]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(Templates::class, ['id' => 'template_id']);
    }

    /**
     * Gets query for [[Facets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacets()
    {
        return $this->hasMany(Facets::class, ['id' => 'facet_id'])->viaTable('element_facets', ['element_id' => 'id']);
    }

    /**
     * Gets query for [[ElementFacets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementFacets()
    {
        return $this->hasMany(ElementFacets::class, ['element_id' => 'id']);
    }

    /**
     * Gets query for [[Indicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicators()
    {
        return $this->hasMany(Indicators::class, ['id' => 'indicator_id'])->viaTable('element_indicators', ['element_id' => 'id']);
    }

    /**
     * Gets query for [[ElementIndicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementIndicators()
    {
        return $this->hasMany(ElementIndicators::class, ['element_id' => 'id']);
    }

    /**
     * Gets query for [[ElementNarrative]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementNarratives()
    {
        return $this->hasOne(ElementNarratives::class, ['element_id' => 'id']);
    }

    /**
     * Gets query for [[ElementDividers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementDividers()
    {
        return $this->hasOne(ElementDividers::class, ['element_id' => 'id']);
    }

    /**
     * Gets query for [[ElementContributions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementContributions()
    {
        return $this->hasOne(ElementContributions::class, ['element_id' => 'id']);
    }
    
    /**
     * Gets query for [[ElementDropdown]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementDropdown()
    {
        return $this->hasOne(ElementDropdown::class, ['element_id' => 'id']);
    }

    /**
     * Gets query for [[ElementDividers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementBulletedList()
    {
        return $this->hasOne(ElementBulletedList::class, ['element_id' => 'id']);
    }
}
