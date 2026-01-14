<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "spaces_synonyms_expansion".
 *
 * @property int $id
 * @property int $spaces_id
 * @property string $display_name
 * @property string $graph_entity
 * @property string $graph_entity_label
 * @property string $expansion_field
 * @property int $enabled
 *
 * @property Spaces $spaces
 */
class SpacesSynonymsExpansion extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'spaces_synonyms_expansion';
    }

    public function rules() {
        return [
            [['display_name', 'graph_entity', 'graph_entity_label', 'expansion_field'], 'required'],
            [['spaces_id'], 'integer'],
            [['spaces_id'], 'required', 'when' => function ($model) {
                return ! $model->isNewRecord;
            }],
            [['display_name', 'graph_entity', 'graph_entity_label', 'expansion_field'], 'string', 'max' => 255],
            [['enabled'], 'boolean'],
            [['enabled'], 'default', 'value' => 1],
            [['spaces_id'], 'exist', 'skipOnError' => true, 'targetClass' => Spaces::class, 'targetAttribute' => ['spaces_id' => 'id']],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'spaces_id' => 'Spaces ID',
            'display_name' => 'Display Name',
            'graph_entity' => 'Graph Entity',
            'graph_entity_label' => 'Graph Entity Label',
            'expansion_field' => 'Expansion Field',
            'enabled' => 'Enabled',
        ];
    }

    /**
     * Gets query for [[Spaces]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpaces() {
        return $this->hasOne(Spaces::class, ['id' => 'spaces_id']);
    }

    /**
     * Builds a Cypher query to fetch synonyms based on search keywords.
     * Matches entities by name field and returns their synonyms field.
     *
     * @param string $keywords Search keywords entered by the user
     * @return string|null The generated synonym query or null if expansion is not enabled
     */
    public function buildSynonymQuery($keywords) {
        if (empty($this->enabled) || empty($this->expansion_field)) {
            return null;
        }

        if (empty($keywords)) {
            return null;
        }

        $entityType = $this->graph_entity;
        $labelField = $this->graph_entity_label; // This is the 'name' field (e.g., 'name')
        $expansionField = $this->expansion_field; // This is the 'synonyms' field

        // Escape the keywords for use in Cypher query
        $escapedKeywords = str_replace("'", "\\'", trim($keywords));

        // Build the query: MATCH entity by name, return synonyms field
        $query = "MATCH (n:{$entityType}) ";
        $query .= "WHERE n.{$labelField} = '{$escapedKeywords}' ";
        $query .= "RETURN n.{$expansionField} AS synonyms";

        return $query;
    }

    /**
     * Creates and populates a set of models.
     * https://github.com/wbraganca/yii2-dynamicform?tab=readme-ov-file#model-class.
     *
     * @param string $modelClass
     * @param array $multipleModels
     * @return array
     */
    public static function createMultipleModels($modelClass, $multipleModels = []) {
        $model = new $modelClass();
        $formName = $model->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if (! empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, 'id', 'id'));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($item['id']) && ! empty($item['id']) && isset($multipleModels[$item['id']])) {
                    $models[] = $multipleModels[$item['id']];
                } else {
                    $models[] = new $modelClass();
                }
            }
        }

        // init models if they are empty
        if (empty($models)) {
            $models = [new $modelClass()];
        }

        return $models;
    }
}
