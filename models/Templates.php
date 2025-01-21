<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "templates".
 *
 * @property int $id
 * @property int $profile_template_category_id
 * @property string $name
 * @property string|null $description
 *  @property string $language
 *
 * @property Elements[] $elements
 * @property ProfileTemplateCategories $profileTemplateCategory
 * @property TemplateElements[] $templateElements
 */
class Templates extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'templates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['profile_template_category_id', 'name', 'url_name','language'], 'required'],
            [['profile_template_category_id'], 'integer'],
            [['description'], 'string'],            
            [['visible'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['language'], 'string', 'max' => 10], // Limit the language string length to 10 characters
            [['language'], 'default', 'value' => 'en'], // Default to 'en'
            [['language'], 'in', 'range' => array_keys(Yii::$app->params['languages'])],
            [['url_name'], 'string', 'max' => 100],
            ['url_name', 'unique', 'message' => 'This url name already exists.'],
            [['profile_template_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProfileTemplateCategories::class, 'targetAttribute' => ['profile_template_category_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'profile_template_category_id' => 'Profile Template Category ID',
            'name' => 'Name',
            'url_name' => 'Url Name',
            'description' => 'Description',
            'language' => 'Language', 
            'visible' => 'Visible',
        ];
    }

    /**
     * Gets query for [[Elements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElements()
    {
        return $this->hasMany(Elements::class, ['template_id' => 'id'])->orderBy(['order' => SORT_ASC]);;
    }

    /**
     * Gets query for [[ProfileTemplateCategory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProfileTemplateCategory()
    {
        return $this->hasOne(ProfileTemplateCategories::class, ['id' => 'profile_template_category_id']);
    }

    public function isHidden() {
        return !$this->visible || !$this->profileTemplateCategory->visible;
    }

}
