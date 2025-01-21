<?php

namespace app\models;

use Yii;

class ElementBulletedList extends \yii\db\ActiveRecord
{
    // exists in ElementBulletedListItem; needed for getConfig()
    public $items;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_bulleted_list}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['element_id'], 'integer'],
            
            [['title'], 'string', 'max' => 255],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['description'], 'string'],
          
            [['elements_number'], 'integer'],

            [['element_id'], 'required'],
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
            'description' => 'Description',
            'elements_number' => 'Maximum allowed number of items in the list',
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

    public function getConfig($element_id, $template_id, $user_id)
    {
        $config = ElementBulletedList::find()->where([ 'element_id' => $element_id ])->one();

        // TODO: fetch with one query: outer join
        $config->items = ElementBulletedListItem::find()
            ->where([
                'element_id' => $element_id,
                'user_id' => $user_id,
                'template_id' => $template_id,
            ])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return $config;
    }

}
