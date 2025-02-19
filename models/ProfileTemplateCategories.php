<?php

namespace app\models;

use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "profile_template_categories".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property boolean $hide
 */
class ProfileTemplateCategories extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'profile_template_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['visible'], 'boolean']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'visible' => 'Visible'
        ];
    }

    public function getTemplates() {
        return $this->hasMany(Templates::class, ['profile_template_category_id' => 'id']);
    }


    public function getTemplateDropdownData() {
        
        return ProfileTemplateCategories::find([ 'id', 'name', 'visible' ])
            ->with(['templates' => function ($query) {
                $query->andWhere(['visible' => true]);
            }])->all();
            
    }
}
