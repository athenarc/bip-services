<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%element_table}}".
 *
 * @property int $id
 * @property int $element_id
 * @property string $title
 * @property string|null $description
 * @property boolean|null $hide_when_empty
 * @property int|null $max_rows
 *
 *
 * @property Elements $element
 * @property ElementTableHeaders[] $ElementTableHeaders
 */
class ElementTable extends \yii\db\ActiveRecord
{

    public $table_data;        // Exists in ElementTableInstances, needed for getConfigTable
    public $last_updated;    // Exists in ElementTableInstances, needed for getConfigTable
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_table}}';
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
            [['title'], 'string', 'max' => 1024],
            [['description'], 'string'],
            [['hide_when_empty'], 'boolean'],
            [['hide_when_empty'], 'default', 'value'=> false],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],
            [['max_rows'], 'integer'],

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
            'heading_type' => 'Header Size',
            'max_rows' => 'Maximum allowed number of rows in the table',
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
     * Gets query for [[ElementTableHeaders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementTableHeaders()
    {
        return $this->hasMany(ElementTableHeaders::class, ['element_table_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }

    public function getConfigTable($element_id, $template_id, $user_id) {

        // eager loading
        $element_config = self::find()->with('elementTableHeaders')->where([ 'element_id' => $element_id ])->one();

        // get info for table instances
        // TODO: fetch with one query: outer join
        $element_instance_config = ElementTableInstances::find()
        ->where([
            'element_id' => $element_id,
            'user_id' => $user_id,
            'template_id' => $template_id,
        ])
        ->one();


        if ($element_instance_config) {
            $element_config->table_data = json_decode($element_instance_config->table_data, true);
            $element_config->last_updated = $element_instance_config->last_updated;
        }

        // print_r($element_config);
        // print_r($element_instance_config);
        // exit;

        return $element_config;
    }
}
