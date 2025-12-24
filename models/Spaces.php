<?php

namespace app\models;

use Yii;

class Spaces extends \yii\db\ActiveRecord {
    public $logo_upload;

    // use the default logo
    public $logo_default = 1;

    // space name as indexed in solr
    public $solr_name;

    // selected annotation IDs for search filtering
    public $selected_annotations = [];

    public $provided_by;

    public static function tableName() {
        return 'spaces';
    }

    public function rules() {
        return [
            [['url_suffix', 'display_name', 'ordering', 'relevance', 'popularity', 'influence', 'cc', 'impulse'], 'required'],
            [['url_suffix', 'display_name', 'ordering', 'relevance', 'popularity', 'influence', 'cc', 'impulse', 'topics'], 'string'],
            ['url_suffix', 'unique', 'message' => 'This url name already exists.'],

            ['ordering', 'in', 'range' => ['popularity', 'influence', 'citation_count', 'impulse', 'year']],
            ['relevance', 'in', 'range' => ['high', 'low']],
            [['popularity', 'influence', 'cc', 'impulse'], 'in', 'range' => ['all', 'top001', 'top01', 'top1', 'top10']],

            [['start_year'], 'integer', 'min' => 1400, 'max' => date('Y') + 1],
            [['end_year'], 'integer', 'min' => 1400, 'max' => date('Y') + 1],
            ['end_year', 'compare', 'compareAttribute' => 'start_year', 'operator' => '>=', 'message' => '{attribute}({value}) must be greater than Start Year'],

            ['topics', 'default', 'value' => null],
            ['type', 'default', 'value' => null],
            ['is_oa', 'default', 'value' => null],
            ['pubmed_types', 'default', 'value' => null],
            [['has_pubmed_types'], 'boolean'],
            [['has_annotations_flag'], 'boolean'],
            [['enable_annotations_flag'], 'boolean'],

            ['logo_upload', 'image', 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024 * 1024, 'wrongExtension' => 'Allowed extensions {extensions}'],

            ['logo_default', 'boolean'],

            ['annotation_db', 'string'],
            ['annotation_db', 'default', 'value' => null],

            ['graph_db_system', 'string'],
            ['graph_db_system', 'default', 'value' => null],

            [['annotation_db', 'graph_db_system'], 'safe'],
            [['annotation_db', 'graph_db_system'], 'validateBothOrNone', 'skipOnEmpty' => false],

            [['theme_color'], 'string', 'max' => 7], // Hex color codes are 7 characters long including the '#'
            [['theme_color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'], // Validate as a hexadecimal color code

            // Like/Dislike records feature
            ['enable_like_dislike_records', 'boolean'],
            // Confirm/Report annotations feature
            ['enable_like_dislike_annotations', 'boolean'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'url_suffix' => 'URL suffix',
            'display_name' => 'Display name',
            'start_year' => 'Start Year',
            'end_year' => 'End Year',
            'ordering' => 'Order By',
            'relevance' => 'Keyword Relevance',
            'popularity' => 'Popularity',
            'influence' => 'Influence',
            'cc' => 'Citation Count',
            'impulse' => 'Impulse',
            'topics' => 'Topics',
            'type' => 'Type',
            'has_pubmed_types' => 'Enable NLM Types',
            'pubmed_types' => 'Pubmed Types',
            'has_annotations_flag' => 'Show annotations filter',
            'enable_annotations_flag' => 'Enable annotations filter',
            'is_oa' => 'Availability',
            'logo_upload' => 'Logo',
            'logo_default' => '',
            'annotation_db' => 'Annotation Database',
            'graph_db_system' => 'Graph Database System',
            'enable_like_dislike_records' => 'Enable Like/Dislike Records Feature',
            'enable_like_dislike_annotations' => 'Enable Confirm/Report Annotations Feature',
        ];
    }

    public function setLogoDefault() {
        if (isset($this->logo)) {
            $this->logo_default = 0;
        } else {
            $this->logo_default = 1;
        }
    }

    public function convertToArray($attribute) {
        $this->$attribute = $this->$attribute !== null ? explode(',', $this->$attribute) : [];
    }

    public function getTypeAsArray() {
        // Convert type to an array
        $this->convertToArray('type');
    }

    public function getIsOaAsArray() {
        // Convert is_oa to an array
        $this->convertToArray('is_oa');
    }

    public function getPubmedTypesAsArray() {
        // Convert pubmed_types to an array
        $this->convertToArray('pubmed_types');
    }

    public function beforeValidate() {
        if (! parent::beforeValidate()) {
            return false;
        }

        // if a checkbox for type, is_oa, pubmed_types is selected
        foreach (['type', 'is_oa'] as $attr) {
            if ($this->$attr !== '') {
                // transform the array (from request) to string
                $this->$attr = implode(',', $this->$attr);
            }
        }

        return true;
    }

    public static function fetchSpaces($id) {
        if (! empty($id)) {
            // Fetch the space model
            $model = self::findOne($id);

            if (! $model) {
                throw new \yii\web\NotFoundHttpException('The requested space does not exist.');
            }
            $model->setLogoDefault();
            $model->getTypeAsArray();
            $model->getIsOaAsArray();
            $model->getPubmedTypesAsArray();
        } else {
            // Create a new space model
            $model = new self();
            // preset only the not null default values
            $model->loadDefaultValues();
        }

        return $model;
    }

    public static function fetchSpacesBySuffix($url_suffix) {
        if (isset($url_suffix) && $url_suffix !== '') {
            $space_model = self::find()->where(['url_suffix' => $url_suffix])->one();

            // if specified space model not found
            if (! $space_model) {
                throw new \yii\web\NotFoundHttpException('Space Not Found');
            }
        } else {
            $space_model = new self();
            // preset all the default values (null included)
            $space_model->loadDefaultNullValues();
        }

        return $space_model;
    }

    public function uploadLogo() {
        // set the default logo
        if ($this->logo_default) {
            // previous logo exists
            if (isset($this->logo)) {
                // remove previous logo
                unlink($this->uploadLogoPath(true) . $this->logo);
            }

            // remove its name from db
            $this->logo = null;

            return true;

        // set new logo (a file is uploaded)
        // A logo image may already exists or not, and a new one is uploaded
        // save the new logo
        } elseif ($this->logo_upload) {
            // previous logo exists
            if (isset($this->logo)) {
                // remove previous logo
                unlink($this->uploadLogoPath(true) . $this->logo);
            }

            // upload new logo and save its name in the db
            $file_name = Yii::$app->security->generateRandomString(8) . $this->logo_upload->baseName . '.' . $this->logo_upload->extension;
            $full_file_path = $this->uploadLogoPath(true) . $file_name;

            if ($this->logo_upload->saveAs($full_file_path)) {
                $this->logo = $file_name;

                return true;
            }

            return false;
        }

        // set new logo (no file uploaded)
        // A logo image already exists, and no newer one is uploaded
        // keep the existing logo
        return true;
    }

    public function uploadLogoPath($full_path_type = null) {
        //  full @web path
        if ($full_path_type) {
            return Yii::getAlias('@webroot/img/spaces/');
        }
        // relative @web path
        return Yii::getAlias('@web/img/spaces/');
    }

    public static function fetchGetRequestArray($space_model, $post_request_array) {
        // the array that will be used for the GET redirection
        $get_request_array = [];

        // only the values that are different between the POST request and the current space model,
        // will appear in the GET request
        // Skip annotations here - it's handled separately below
        foreach ($space_model->toArray() as $key => $value) {
            if ($key === 'annotations') {
                continue; // Skip annotations, handled separately
            }

            if (array_key_exists($key, $post_request_array)) {
                // special handling for topics, type, is_oa, pubmed_types
                if ($key === 'topics' or $key === 'type' or $key === 'is_oa' or $key == 'pubmed_types') {
                    // sort arrays before comparison
                    $post_array = $post_request_array[$key];
                    sort($post_array);
                    sort($value);

                    if ($post_array !== $value) {
                        if ($post_request_array[$key] === []) {
                            // an empty array [] doesn't appear in the GET request, so we use [""] instead,
                            // we then remove it with array_filter(), after the request has been made
                            $get_request_array[$key] = [''];
                        } else {
                            $get_request_array[$key] = $post_request_array[$key];
                        }
                    }
                } elseif ($post_request_array[$key] !== $value) {
                    $get_request_array[$key] = $post_request_array[$key];
                }
            }
        }

        // Handle annotations separately (not a Spaces model field)
        // Always include annotations in GET params if present in POST
        if (array_key_exists('annotations', $post_request_array)) {
            $post_annotations = $post_request_array['annotations'];

            if (empty($post_annotations)) {
                // Empty array - user deselected all
                $get_request_array['annotations'] = [''];
            } else {
                $get_request_array['annotations'] = $post_annotations;
            }

            // If enable_annotations_flag is set and true, set to all annotation IDs
        // filtering with annotations is done from the admin panel
        } elseif (array_key_exists('enable_annotations_flag', $post_request_array) &&
                  $post_request_array['enable_annotations_flag']) {
            $enabled_annotations = $space_model->annotations;

            if (! empty($enabled_annotations)) {
                $get_request_array['annotations'] = array_column($enabled_annotations, 'id');
            }
        }

        return $get_request_array;
    }

    public function loadDefaultNullValues() {
        // same to the yii2 method loadDefaultValues,
        // but it also loads null default values
        $columns = static::getTableSchema()->columns;

        foreach ($this->attributes() as $name) {
            if (isset($columns[$name])) {
                $defaultValue = $columns[$name]->defaultValue;
                $this->setAttribute($name, $defaultValue);
            }
        }

        return $this;
    }

    public function prepareForRequest() {
        $this->solr_name = Yii::$app->params['spaceSolrNames'][$this->url_suffix] ?? null;

        // Convert topics to an array
        // split the string into an array using ',' as the delimiter and trim each element to remove leading and trailing spaces
        $this->topics = $this->topics !== null ? array_filter(array_map('trim', explode(',', $this->topics))) : [];

        // Convert type, is_oa, pubmed_types to an array
        $this->getTypeAsArray();
        $this->getIsOaAsArray();
        $this->getPubmedTypesAsArray();

        // convert null->'' and integer->string
        $this->start_year = ($this->start_year === null) ? '' : strval($this->start_year);
        $this->end_year = ($this->end_year === null) ? '' : strval($this->end_year);

        return $this;
    }

    public static function findAnnotationIds($query, $papers) {
        $pids = [];

        if (strpos($query, '$dois') !== false) {
            $pids = ['dois' => array_column($papers, 'doi')];
        } elseif (strpos($query, '$ids') !== false) {
            $pids = [
                'ids' => array_map(function ($id) {
                    return substr(strrchr($id, '|'), 1); // Get part after "|"
                }, array_column($papers, 'openaire_id'))
            ];
        } else {
            throw new Exception("Invalid query: must contain either '\$dois' or '\$ids'");
        }

        return $pids;
    }

    public static function runAnnotationsQuery($papers, $annotation_db_name, $graph_db_system, $space_annotations) {
        $annotation_db = Yii::$app->params['annotation_dbs'][$annotation_db_name];

        $conn = GraphConnectionFactory::createConnection($graph_db_system, $annotation_db);

        $data = [];

        foreach ($space_annotations as $space_annotation) {
            // get the appropriate id types based on the annotation query (e.g., dois or openaire_ids)
            $pids = self::findAnnotationIds($space_annotation->query, $papers);

            // run the actual annotation query
            [ $stats, $rows ] = $conn->run($space_annotation->query, $pids);

            // enrich with annotations
            $data[] = self::enrichAnnotations($rows, $space_annotation);
        }

        return $data;
    }

    public static function fetchAnnotations($papers, $space_model) {
        $space_annotations = $space_model->annotations;

        if (empty($space_annotations)) {
            return $papers;
        }

        // give dois as query param
        // one array per annotation query
        $dois_to_annotations_db_multiple = self::runAnnotationsQuery($papers, $space_model->annotation_db, $space_model->graph_db_system, $space_annotations);

        // Group by doi per annotation
        $dois_to_annotations_multiple = [];

        foreach ($dois_to_annotations_db_multiple as $annotation_row => $dois_to_annotations_db) {
            foreach ($dois_to_annotations_db as $row => $row_data) {
                $doi = $row_data[0];
                $annotation_data = $row_data[1];
                $dois_to_annotations_multiple[$annotation_row][$doi] = $annotation_data;
            }
        }

        foreach ($papers as $paper => $paper_data) {
            $doi = $paper_data['doi'];
            $openaire_id = substr(strrchr($paper_data['openaire_id'], '|'), 1); // openaire id in our db have an extra prefix "|"

            $annotations = [];

            foreach ($dois_to_annotations_multiple as $annotation_row => $dois_to_annotations) {
                // first merge by doi
                if (array_key_exists($doi, $dois_to_annotations)) {
                    $annotations = array_merge($annotations, $dois_to_annotations[$doi]);

                // if doi not found, try to find it by openaire_id
                } elseif (array_key_exists($openaire_id, $dois_to_annotations)) {
                    $annotations = array_merge($annotations, $dois_to_annotations[$openaire_id]);
                }
            }
            // Create annotation key to input array
            $papers[$paper]['annotations'] = $annotations;
        }

        return $papers;
    }

    public static function getSearchParams($space_url_suffix) {
        $space_model = self::fetchSpacesBySuffix($space_url_suffix);
        $space_model->prepareForRequest();

        // all GET params
        $get_data_all = Yii::$app->request->get();
        // print_r($get_data_all);

        // merge the GET params into the space_model, to create the final SearchForm parameters
        $space_model->setAttributes($get_data_all, false);

        // Parse request parameters that are not database fields
        $space_model->parseAnnotations(
            $get_data_all['annotations'] ?? null,
            $get_data_all['enable_annotations_flag'] ?? null
        );
        $space_model->parseProvidedBy($get_data_all['provided_by'] ?? null);

        $search_params = $space_model->toArray();

        // revert topics, type, is_oa, pubmed_types set in fetchGetRequestArray ([""] -> [])
        foreach (['topics', 'type', 'is_oa', 'pubmed_types'] as $key) {
            if (array_key_exists($key, $search_params) && $search_params[$key] === ['']) {
                $search_params[$key] = [];
            }
        }

        // add keywords
        if (array_key_exists('keywords', $get_data_all)) {
            $search_params['keywords'] = $get_data_all['keywords'];
        } else {
            // when loading the page without search query
            $search_params['keywords'] = '';
        }

        // not used
        $search_params['location'] = (Yii::$app->request->get('location') == null || Yii::$app->request->get('location') == '') ? 'title-abstract' : Yii::$app->request->get('location');

        // Get annotations and provided_by from prepareForRequest() (already parsed)
        $search_params['annotations'] = $space_model->selected_annotations;
        $search_params['provided_by'] = $space_model->provided_by;

        return [
            $search_params,
            $space_model
        ];
    }

    public function getAnnotations() {
        return $this->hasMany(SpacesAnnotations::class, ['spaces_id' => 'id'])->where(['enabled' => 1]);
    }

    public function getAllAnnotations() {
        return $this->hasMany(SpacesAnnotations::class, ['spaces_id' => 'id']);
    }

    /**
     * Get annotation descriptions as an array.
     * @return array Array of annotation descriptions
     */
    public function getEnabledAnnotationNames() {
        $all_annotations = $this->hasMany(SpacesAnnotations::class, ['spaces_id' => 'id'])->all();
        $annotation_descriptions = [];

        if (! empty($all_annotations)) {
            foreach ($all_annotations as $annotation) {
                if (! empty($annotation->description)) {
                    $annotation_descriptions[] = $annotation->description;
                }
            }
        }

        return $annotation_descriptions;
    }

    /**
     * Get annotation IDs and descriptions as an associative array.
     * @return array Array with annotation_id as key and description as value
     */
    public function getEnabledAnnotationMap() {
        $all_annotations = $this->hasMany(SpacesAnnotations::class, ['spaces_id' => 'id'])->all();
        $annotation_map = [];

        if (! empty($all_annotations)) {
            foreach ($all_annotations as $annotation) {
                if (! empty($annotation->description)) {
                    $annotation_map[$annotation->id] = $annotation->description;
                }
            }
        }

        return $annotation_map;
    }

    /**
     * Custom validator to check that either both fields are empty or both are filled.
     */
    public function validateBothOrNone($attribute, $params, $validator) {
        if (($this->annotation_db && ! $this->graph_db_system) || (! $this->annotation_db && $this->graph_db_system)) {
            $this->addError($attribute, 'Both "Annotation Database" and "Graph Database System" fields must be set.');
        }
    }

    /**
     * Parse annotations parameter from GET request
     * Handles special cases like -1 (select all), empty arrays, and enable_annotations_flag.
     * @param array|null $annotations Annotations array from GET params
     * @param bool|null $enable_annotations_flag Enable annotations flag from GET params
     */
    protected function parseAnnotations($annotations = null, $enable_annotations_flag = null) {
        $annotations_was_provided = $annotations !== null;
        $annotations_explicitly_empty = false;

        if ($annotations_was_provided) {
            $annotations_raw = is_array($annotations) ? $annotations : [];

            // Check if annotations was explicitly set to empty (user deselected all)
            // It could be [] or [''] (the latter is used to ensure empty arrays appear in GET)
            if (empty($annotations_raw) || (count($annotations_raw) === 1 && $annotations_raw[0] === '')) {
                $annotations_explicitly_empty = true;
                $this->selected_annotations = [];
            } else {
                $this->selected_annotations = array_values(array_filter($annotations_raw));
            }
        } else {
            $this->selected_annotations = [];
        }

        // Convert enable_annotations_flag to all annotation IDs (only for initialization)
        // This happens when enable_annotations_flag is set but annotations array is empty
        if ($enable_annotations_flag !== null &&
            $enable_annotations_flag &&
            empty($this->selected_annotations) &&
            ! $annotations_explicitly_empty) {
            // Get all enabled annotation IDs
            $enabled_annotations = $this->getAnnotations()->all();

            if (! empty($enabled_annotations)) {
                $this->selected_annotations = array_column($enabled_annotations, 'id');
            }
        }

        // If space has enable_annotations_flag set and annotations were not provided in GET params,
        // select all annotation IDs (only on initial page load, not when user explicitly deselected)
        if ($this->enable_annotations_flag &&
            empty($this->selected_annotations) &&
            ! $annotations_was_provided &&
            ! $annotations_explicitly_empty) {
            // Get all enabled annotation IDs
            $enabled_annotations = $this->getAnnotations()->all();

            if (! empty($enabled_annotations)) {
                $this->selected_annotations = array_column($enabled_annotations, 'id');
            }
        }
    }

    /**
     * Parse provided_by parameter from GET request.
     * @param array|null $provided_by Provided by array from GET params
     */
    protected function parseProvidedBy($provided_by = null) {
        if ($provided_by !== null) {
            $this->provided_by = is_array($provided_by) ? $provided_by : [];
        } else {
            $this->provided_by = [];
        }
    }

    private static function enrichAnnotations($rows, $space_annotation) {
        // add annotation color, description
        foreach ($rows as $row => $row_data) {
            $doi = $row_data[0];
            $annotations = $row_data[1];

            foreach ($annotations as $annotation_row => $annotation_data) {
                $rows[$row][1][$annotation_row]['annotation_id'] = $space_annotation['id'];
                $rows[$row][1][$annotation_row]['annotation_color'] = $space_annotation['color'];
                $rows[$row][1][$annotation_row]['annotation_description'] = $space_annotation['description'];
                $rows[$row][1][$annotation_row]['has_reverse_query'] = ! empty($space_annotation['reverse_query']);
            }
        }

        return $rows;
    }
}
