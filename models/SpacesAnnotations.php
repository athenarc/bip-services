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
 * @property string|null $display_name_plural
 * @property string|null $description
 * @property string|null $color
 * @property string|null $query
 * @property string|null $graph_entity
 * @property string|null $graph_entity_identifier
 * @property string|null $graph_entity_label
 * @property string|null $metadata_fields
 * @property int $perform_search_expansion
 * @property string|null $expansion_field
 * @property int $enabled
 *
 * @property Spaces $spaces
 */
class SpacesAnnotations extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'spaces_annotations';
    }

    public function rules() {
        return [
            // [['spaces_id'], 'required'],
            // [['spaces_id'], 'integer'],
            // [['spaces_id'], 'exist', 'skipOnError' => true, 'targetClass' => Spaces::class, 'targetAttribute' => ['spaces_id' => 'id']],
            [['query'], 'required'],
            [['query', 'description'], 'string'],
            [['name', 'display_name_plural', 'graph_entity', 'graph_entity_identifier', 'graph_entity_label', 'expansion_field'], 'string', 'max' => 255],
            [['metadata_fields'], 'string', 'max' => 500],
            [['graph_entity', 'graph_entity_identifier', 'graph_entity_label'], 'required'],
            [['color'], 'string', 'max' => 7], // Hex color codes are 7 characters long including the '#'
            [['color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'], // Validate as a hexadecimal color code
            [['enabled', 'perform_search_expansion'], 'boolean'],
            [['enabled'], 'default', 'value' => 1],
            [['perform_search_expansion'], 'default', 'value' => 0],
            [['expansion_field'], 'required', 'when' => function($model) {
                return !empty($model->perform_search_expansion);
            }, 'whenClient' => "function (attribute, value) {
                var fieldId = attribute.id || '';
                var indexMatch = fieldId.match(/\\d+/);
                if (!indexMatch) return false;
                var index = indexMatch[0];
                var checkboxSelector = 'input[name*=\"[' + index + ']perform_search_expansion\"]';
                var checkbox = $(checkboxSelector);
                return checkbox.length > 0 && checkbox.is(':checked');
            }"],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'spaces_id' => 'Spaces ID',
            'name' => 'Name',
            'display_name_plural' => 'Display name (plural)',
            'description' => 'Description',
            'color' => 'Color',
            'query' => 'Query',
            'graph_entity' => 'Graph entity',
            'graph_entity_identifier' => 'Graph entity identifier',
            'graph_entity_label' => 'Graph entity label',
            'metadata_fields' => 'Metadata fields',
            'perform_search_expansion' => 'Perform search expansion',
            'expansion_field' => 'Expansion field',
            'enabled' => 'Enabled',
        ];
    }

    /**
     * Validates query syntax for annotation queries.
     * Checks if query has doi/DOI in RETURN and if COLLECT has id and label properties.
     *
     * @param string $query The query string to validate
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateQuerySyntax($query) {
        if (empty($query)) {
            return ['valid' => false, 'errors' => ['Query is empty']];
        }

        $errors = [];

        // Check if query contains RETURN clause
        if (! preg_match('/RETURN/i', $query)) {
            $errors[] = 'Query must contain a RETURN clause';

            return ['valid' => false, 'errors' => $errors];
        }

        // Extract everything after RETURN (until ORDER BY/LIMIT/SKIP or end)
        if (preg_match(
            // Capture everything after RETURN up to ORDER BY / LIMIT / SKIP or end of string
            '/RETURN\s+(.+?)(?=\s+(?:ORDER\s+BY|LIMIT|SKIP)|$)/is',
            $query,
            $matches
        )) {
            $returnClause = trim($matches[1]);

            // Check 1: Must contain doi or DOI
            if (! preg_match('/\bdoi\b/i', $returnClause)) {
                $errors[] = 'RETURN clause must contain doi or DOI property';
            }

            // Check 2: Must have COLLECT
            if (! preg_match('/COLLECT/i', $returnClause)) {
                $errors[] = 'Query must contain COLLECT';
            } else {
                // Find COLLECT and extract its content (handle nested brackets)
                $collectStart = stripos($returnClause, 'COLLECT');
                $afterCollect = substr($returnClause, $collectStart);

                // Find opening bracket
                $openPos = strpos($afterCollect, '(');

                if ($openPos === false) {
                    $openPos = strpos($afterCollect, '{');
                    $closeChar = '}';
                } else {
                    $closeChar = ')';
                }

                if ($openPos !== false) {
                    // Extract content inside COLLECT brackets (handle nested)
                    $collectContent = '';
                    $depth = 0;

                    for ($i = $openPos + 1; $i < strlen($afterCollect); $i++) {
                        $char = $afterCollect[$i];

                        if ($char === $closeChar && $depth === 0) {
                            break;
                        }

                        if ($char === '(' || $char === '{') {
                            $depth++;
                        } elseif ($char === ')' || $char === '}') {
                            $depth--;
                        }
                        $collectContent .= $char;
                    }

                    // Check for id property at top level of COLLECT (not nested in arrays [])
                    // Must be "id: <value>" or "id = <value)" and value must not be empty
                    $hasId = false;

                    if (preg_match_all('/\bid\s*[:=]/i', $collectContent, $idMatches, PREG_OFFSET_CAPTURE)) {
                        foreach ($idMatches[0] as $match) {
                            $pos = $match[1];
                            // Check if before this position there are unmatched [ brackets
                            $before = substr($collectContent, 0, $pos);
                            $openBrackets = substr_count($before, '[') - substr_count($before, ']');
                            // If no unmatched [ brackets, it's top-level
                            if ($openBrackets == 0) {
                                // Now check that id has a non-empty value after : or =
                                $fromId = substr($collectContent, $pos);

                                if (preg_match('/\bid\s*[:=]\s*([^,\]\}]+)/i', $fromId, $valueMatch)) {
                                    $idValue = trim($valueMatch[1]);

                                    if ($idValue !== '') {
                                        $hasId = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if (! $hasId) {
                        $errors[] = 'COLLECT must contain id property with non-empty value';
                    }

                    // Check for label property at top level of COLLECT (not nested in arrays [])
                    // Must be "label: <value>" or "label = <value)" and value must not be empty
                    $hasLabel = false;

                    if (preg_match_all('/\blabel\s*[:=]/i', $collectContent, $labelMatches, PREG_OFFSET_CAPTURE)) {
                        foreach ($labelMatches[0] as $match) {
                            $pos = $match[1];
                            // Check if before this position there are unmatched [ brackets
                            $before = substr($collectContent, 0, $pos);
                            $openBrackets = substr_count($before, '[') - substr_count($before, ']');
                            // If no unmatched [ brackets, it's top-level
                            if ($openBrackets == 0) {
                                // Now check that label has a non-empty value after : or =
                                $fromLabel = substr($collectContent, $pos);

                                if (preg_match('/\blabel\s*[:=]\s*([^,\]\}]+)/i', $fromLabel, $valueMatch)) {
                                    $labelValue = trim($valueMatch[1]);

                                    if ($labelValue !== '') {
                                        $hasLabel = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if (! $hasLabel) {
                        $errors[] = 'COLLECT must contain label property with non-empty value';
                    }
                } else {
                    $errors[] = 'COLLECT syntax is invalid';
                }
            }
        } else {
            $errors[] = 'Query must contain a RETURN clause';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Builds metadata query from graph entity fields.
     * If metadata_fields is empty, returns all fields of the entity.
     *
     * @return string The generated metadata query
     */
    public function buildMetadataQuery() {
        $entityType = $this->graph_entity;
        $identifierField = $this->graph_entity_identifier;
        $labelField = $this->graph_entity_label;

        // Parse metadata fields (comma-separated)
        $fields = [];

        if (! empty($this->metadata_fields)) {
            $fields = array_map('trim', explode(',', $this->metadata_fields));
            $fields = array_filter($fields); // Remove empty values
        }

        // If metadata_fields is empty, get all fields dynamically using keys()
        if (empty($fields)) {
            // Use Cypher list comprehension to get all properties dynamically
            // Format: [key IN keys(n) | {label: key, value: n[key]}]
            $dataArray = "[key IN keys(n) | {label: key, value: coalesce(n[key], '')}]";
        } else {
            // Build data array with specified metadata fields
            $dataItems = [];

            foreach ($fields as $field) {
                $field = trim($field);

                if (! empty($field)) {
                    // Escape single quotes in field names
                    $escapedField = str_replace("'", "\\'", $field);
                    // Return value as-is (can be string, number, array, etc.)
                    // Arrays will be preserved and handled in PHP display code
                    $dataItems[] = "{label: '{$escapedField}', value: coalesce(n.{$field}, '')}";
                }
            }
            $dataArray = '[' . implode(', ', $dataItems) . ']';
        }

        // Build the full Cypher query
        // Format: MATCH (n:EntityType {identifierField: $annotation_id}) RETURN {label: n.labelField, data: [...]}
        $query = "MATCH (n:{$entityType} {{$identifierField}: \$annotation_id}) ";
        $query .= "RETURN {label: n.{$labelField}, data: {$dataArray}}";

        return $query;
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

        unset($model, $formName, $post);

        return $models;
    }
}
