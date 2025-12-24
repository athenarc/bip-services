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
            [['query', 'reverse_query', 'reverse_query_count', 'reverse_query_info'], 'string'],
            [['name', 'description'], 'string', 'max' => 255],
            [['color'], 'string', 'max' => 7], // Hex color codes are 7 characters long including the '#'
            [['color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'], // Validate as a hexadecimal color code
            [['enabled'], 'boolean'],
            [['enabled'], 'default', 'value' => 1],

            [['reverse_query', 'reverse_query_count', 'reverse_query_info'], 'validateReverseFields'],
        ];
    }

    public function attributeLabels() {
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
            'enabled' => 'Enabled',
        ];
    }

    /**
     * Custom validation function to check that if one reverse_query field is filled, all are required.
     */
    public function validateReverseFields($attribute, $params, $validator) {
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
        if (!preg_match('/RETURN/i', $query)) {
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
            if (!preg_match('/\bdoi\b/i', $returnClause)) {
                $errors[] = 'RETURN clause must contain doi or DOI property';
            }
            
            // Check 2: Must have COLLECT
            if (!preg_match('/COLLECT/i', $returnClause)) {
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
                    if (!$hasId) {
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
                    if (!$hasLabel) {
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
