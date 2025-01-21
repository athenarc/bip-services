<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "spaces_annotations".
 *
 * @property int $id
 * @property int $spaces_id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $color
 * @property string|null $query
 * @property string|null $reverse_query
 * @property string|null $reverse_query_count
 * @property string|null $reverse_query_info
 *
 * @property Spaces $spaces
 */
class SpacesAnnotations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'spaces_annotations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['spaces_id'], 'required'],
            // [['spaces_id'], 'integer'],
            // [['spaces_id'], 'exist', 'skipOnError' => true, 'targetClass' => Spaces::class, 'targetAttribute' => ['spaces_id' => 'id']],
            [['query'], 'required'],
            [['query', 'reverse_query', 'reverse_query_count', 'reverse_query_info'], 'string'],
            [['name', 'description'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 7], // Hex color codes are 7 characters long including the '#'
            [['color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'], // Validate as a hexadecimal color code

            [['reverse_query', 'reverse_query_count', 'reverse_query_info'], 'validateReverseFields'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'spaces_id' => 'Spaces ID',
            'name' => 'Name',
            'description' => 'Description',
            'color' => 'Color',
            'query' => 'Query',
            'reverse_query' => 'Reverse Query',
            'reverse_query_count' => 'Reverse Query Count',
            'reverse_query_info' => 'Reverse Query Info',
        ];
    }


    /**
     * Custom validation function to check that if one reverse_query field is filled, all are required.
     */
    public function validateReverseFields($attribute, $params, $validator)
    {
        // If any one of these fields is filled, ensure that all are filled
        $filledFields = array_filter([
            $this->reverse_query,
            $this->reverse_query_count,
            $this->reverse_query_info,
        ]);

        if (count($filledFields) > 0 && count($filledFields) < 3) {
            $this->addError($attribute, 'If one of "Reverse Query", "Reverse Query Count", or "Reverse Query Info" is provided, all three fields are required.');
        }
    }

    /**
     * Gets query for [[Spaces]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpaces()
    {
        return $this->hasOne(Spaces::class, ['id' => 'spaces_id']);
    }


        /**
     * Creates and populates a set of models.
     * https://github.com/wbraganca/yii2-dynamicform?tab=readme-ov-file#model-class
     *
     * @param string $modelClass
     * @param array $multipleModels
     * @return array
     */
    public static function createMultipleModels($modelClass, $multipleModels = [])
    {
        $model    = new $modelClass;
        $formName = $model->formName();
        $post     = Yii::$app->request->post($formName);
        $models   = [];

        if (! empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, 'id', 'id'));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($item['id']) && !empty($item['id']) && isset($multipleModels[$item['id']])) {
                    $models[] = $multipleModels[$item['id']];
                } else {
                    $models[] = new $modelClass;
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }
}
