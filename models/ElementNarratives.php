<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "element_narratives".
 *
 * @property int $id
 * @property int $element_id
 * @property string $title
 * @property string $heading_type
 * @property string|null $description
 * @property boolean|null $hide_when_empty
 *
 * @property Elements $element
 */
class ElementNarratives extends \yii\db\ActiveRecord
{
    const TYPE_WORDS = 0;
    const TYPE_CHARACTERS = 1;
    const COUNT_MESSAGE = "Your text is over the limit - text that exceeds this limit is not displayed in the public profile page.";

    public $value; // Exists in ElementNarrativeInstances, needed for getConfigNarrative
    public $last_updated; // Exists in ElementNarrativeInstances, needed for getConfigNarrative
    
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
            [['title'], 'string', 'max' => 1024],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['hide_when_empty'], 'boolean'],
            [['hide_when_empty'], 'default', 'value'=> false],
            [['limit_value'], 'integer', 'min' => 0],
            [['limit_type'], 'in', 'range' => [self::TYPE_WORDS, self::TYPE_CHARACTERS]],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],
        ];
    }

    public static function getLimitTypeList()
    {
        return [
            self::TYPE_WORDS => 'Words',
            self::TYPE_CHARACTERS => 'Characters',
        ];
    }

    public function getLimitTypeName()
    {
        $types = self::getLimitTypeList();
        return $types[$this->limit_type];
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
            'hide_when_empty' => 'Hide when Empty',
            'limit_value' => 'Limit Value',
            'limit_type' => 'Limit Type'
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
        $element_instance_config = ElementNarrativeInstances::find()
        ->where([
            'element_id' => $element_id,
            'user_id' => $user_id,
            'template_id' => $template_id,
        ])
        ->one();

        if ($element_instance_config) {
            $element_config->value = $element_instance_config->value;
            $element_config->limit_type = $element_config->limit_type;
            $element_config->limit_value = $element_config->limit_value;
            $element_config->last_updated = $element_instance_config->last_updated;
        }

        return $element_config;
    }

    public function countText($limit_type, $clean_text) {
        if ($limit_type == ElementNarratives::TYPE_WORDS) {
            return str_word_count($clean_text);
        } elseif ($limit_type == ElementNarratives::TYPE_CHARACTERS) {
            return mb_strlen($clean_text);
        } else {
            throw new \Exception('Invalid limit type');
        }
    }

    public function getLimitStatus($text_value, $limit_value) {
        if ($limit_value && $text_value > $limit_value) {
            return ElementNarratives::COUNT_MESSAGE;
        }
        return null;
    }

    public function countMessage($limit_type, $text_value, $limit_value) {
        return "{$text_value} " . ($limit_value ? "out of {$limit_value} " : "") . "{$limit_type}";
    }
}
