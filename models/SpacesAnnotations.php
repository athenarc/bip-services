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
 * @property int $enabled
 * @property int $enable_facet
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
            [['name', 'display_name_plural', 'graph_entity', 'graph_entity_identifier', 'graph_entity_label'], 'string', 'max' => 255],
            [['metadata_fields'], 'string', 'max' => 500],
            // graph_entity, graph_entity_identifier, and graph_entity_label are optional
            // If not provided, the annotation details page and "Show all relevant works" link will be disabled
            [['color'], 'string', 'max' => 7], // Hex color codes are 7 characters long including the '#'
            [['color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'], // Validate as a hexadecimal color code
            [['enabled'], 'boolean'],
            [['enabled'], 'default', 'value' => 1],
            [['enable_facet'], 'boolean'],
            [['enable_facet'], 'default', 'value' => 0],
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
            'enabled' => 'Enabled',
            'enable_facet' => 'Enable facet',
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

            // Check 2: Must have COLLECT or list comprehension with id and label
            $hasCollect = preg_match('/COLLECT/i', $returnClause);
            // Improved regex to detect list comprehension: [var IN ...] or [var WHERE ...]
            // Also check for patterns like [x IN [...] WHERE ...] with nested brackets
            $hasListComprehension = preg_match('/\[\s*\w+\s+(?:IN|WHERE)\s+/i', $returnClause);
            
            if (! $hasCollect && ! $hasListComprehension) {
                $errors[] = 'Query must contain COLLECT or list comprehension with id and label properties';
            } else {
                $contentToCheck = '';
                $isCollect = false;
                
                if ($hasCollect) {
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
                        $contentToCheck = '';
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
                            $contentToCheck .= $char;
                        }
                        $isCollect = true;
                    } else {
                        $errors[] = 'COLLECT syntax is invalid';
                    }
                } else {
                    // For list comprehension, check the entire RETURN clause for id and label
                    // The list comprehension should contain objects with id and label
                    $contentToCheck = $returnClause;
                    $isCollect = false;
                }

                if ($contentToCheck !== '') {
                    // Check for id property
                    // For COLLECT: must be at top level (not nested in arrays [])
                    // For list comprehension: can be inside objects within the list (more flexible)
                    $hasId = false;

                    if (preg_match_all('/\bid\s*[:=]/i', $contentToCheck, $idMatches, PREG_OFFSET_CAPTURE)) {
                        foreach ($idMatches[0] as $match) {
                            $pos = $match[1];
                            // Check if before this position there are unmatched [ brackets
                            $before = substr($contentToCheck, 0, $pos);
                            $openBrackets = substr_count($before, '[') - substr_count($before, ']');
                            
                            // For COLLECT: must be at top level (openBrackets == 0)
                            // For list comprehension: allow id inside objects (can be nested deeper due to CASE statements)
                            // Check if we're inside a { } object (not just counting brackets)
                            $beforePos = substr($contentToCheck, 0, $pos);
                            $openBraces = substr_count($beforePos, '{') - substr_count($beforePos, '}');
                            
                            if ($isCollect) {
                                // COLLECT: must be top level
                                if ($openBrackets == 0) {
                                    $fromId = substr($contentToCheck, $pos);
                                    if (preg_match('/\bid\s*[:=]\s*([^,\]\}]+)/i', $fromId, $valueMatch)) {
                                        $idValue = trim($valueMatch[1]);
                                        $idValue = trim($idValue, '\'"');
                                        if ($idValue !== '' && $idValue !== 'null') {
                                            $hasId = true;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                // List comprehension: allow id inside objects (openBraces > 0 means inside an object)
                                // The id should be inside a { } object structure
                                if ($openBraces > 0) {
                                    $fromId = substr($contentToCheck, $pos);
                                    if (preg_match('/\bid\s*[:=]\s*([^,\]\}]+)/i', $fromId, $valueMatch)) {
                                        $idValue = trim($valueMatch[1]);
                                        $idValue = trim($idValue, '\'"');
                                        if ($idValue !== '' && $idValue !== 'null') {
                                            $hasId = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (! $hasId) {
                        $errorMsg = $isCollect 
                            ? 'COLLECT must contain id property with non-empty value'
                            : 'List comprehension must contain objects with id property with non-empty value';
                        $errors[] = $errorMsg;
                    }

                    // Check for label property
                    // For COLLECT: must be at top level (not nested in arrays [])
                    // For list comprehension: can be inside objects within the list (more flexible)
                    $hasLabel = false;

                    if (preg_match_all('/\blabel\s*[:=]/i', $contentToCheck, $labelMatches, PREG_OFFSET_CAPTURE)) {
                        foreach ($labelMatches[0] as $match) {
                            $pos = $match[1];
                            // Check if before this position there are unmatched [ brackets
                            $before = substr($contentToCheck, 0, $pos);
                            $openBrackets = substr_count($before, '[') - substr_count($before, ']');
                            
                            // For COLLECT: must be at top level (openBrackets == 0)
                            // For list comprehension: allow label inside objects (can be nested deeper due to CASE statements)
                            // Check if we're inside a { } object (not just counting brackets)
                            $beforePos = substr($contentToCheck, 0, $pos);
                            $openBraces = substr_count($beforePos, '{') - substr_count($beforePos, '}');
                            
                            if ($isCollect) {
                                // COLLECT: must be top level
                                if ($openBrackets == 0) {
                                    $fromLabel = substr($contentToCheck, $pos);
                                    if (preg_match('/\blabel\s*[:=]\s*([^,\]\}]+)/i', $fromLabel, $valueMatch)) {
                                        $labelValue = trim($valueMatch[1]);
                                        $labelValue = trim($labelValue, '\'"');
                                        if ($labelValue !== '' && $labelValue !== 'null') {
                                            $hasLabel = true;
                                            break;
                                        }
                                    }
                                }
                            } else {
                                // List comprehension: allow label inside objects (openBraces > 0 means inside an object)
                                // The label should be inside a { } object structure
                                if ($openBraces > 0) {
                                    $fromLabel = substr($contentToCheck, $pos);
                                    if (preg_match('/\blabel\s*[:=]\s*([^,\]\}]+)/i', $fromLabel, $valueMatch)) {
                                        $labelValue = trim($valueMatch[1]);
                                        $labelValue = trim($labelValue, '\'"');
                                        if ($labelValue !== '' && $labelValue !== 'null') {
                                            $hasLabel = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (! $hasLabel) {
                        $errorMsg = $isCollect 
                            ? 'COLLECT must contain label property with non-empty value'
                            : 'List comprehension must contain objects with label property with non-empty value';
                        $errors[] = $errorMsg;
                    }
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
     * Check if all required graph entity fields are set.
     * @return bool
     */
    public function hasGraphEntityFields() {
        return ! empty($this->graph_entity) &&
               ! empty($this->graph_entity_identifier) &&
               ! empty($this->graph_entity_label);
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
