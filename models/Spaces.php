<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

class Spaces extends \yii\db\ActiveRecord
{

    public $logo_upload;

    // use the default logo
    public $logo_default = 1;

    // space name as indexed in solr
    public $solr_name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'spaces';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url_suffix', 'display_name', 'ordering', 'relevance', 'popularity', 'influence', 'cc', 'impulse'], 'required'],
            [['url_suffix', 'display_name', 'ordering', 'relevance', 'popularity', 'influence', 'cc', 'impulse', 'topics'], 'string'],
            ['url_suffix', 'unique', 'message' => 'This url name already exists.'],

            ['ordering', 'in', 'range' => ["popularity", "influence", "citation_count", "impulse", "year"]],
            ['relevance', 'in', 'range' => ["high", "low"]],
            [['popularity', 'influence', 'cc', 'impulse'], 'in', 'range' => ["all", "top001", "top01", "top1", "top10"]],


            [['start_year'], 'integer', 'min' => 1400, 'max' => date('Y') + 1],
            [['end_year'], 'integer', 'min' => 1400, 'max' => date('Y') + 1],
            ['end_year', 'compare', 'compareAttribute' => 'start_year', 'operator' => '>=', 'message' => '{attribute}({value}) must be greater than Start Year'],

            ['topics', 'default', 'value' => null],
            ['type', 'default', 'value' => null],

            ['logo_upload', 'image', 'extensions' => 'png, jpg, jpeg', 'maxSize' => 1024*1024, 'wrongExtension' => 'Allowed extensions {extensions}'],

            ['logo_default', 'boolean'],

            ['annotation_db', 'string'],
            ['annotation_db', 'default', 'value' => null],

            ['graph_db_system', 'string'],
            ['graph_db_system', 'default', 'value' => null],
            
            [['annotation_db', 'graph_db_system'], 'safe'],
            [['annotation_db', 'graph_db_system'], 'validateBothOrNone', 'skipOnEmpty' => false],   

            [['theme_color'], 'string', 'max' => 7], // Hex color codes are 7 characters long including the '#'
            [['theme_color'], 'match', 'pattern' => '/^#[0-9a-fA-F]{6}$/'], // Validate as a hexadecimal color code

        ];
    }


    public function attributeLabels()
    {
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
            'logo_upload' => 'Logo',
            'logo_default' => '',
            'annotation_db' => 'Annotation Database',
            'graph_db_system' => 'Graph Database System'
        ];
    }

    public function setLogoDefault(){

        if(isset($this->logo)){
            $this->logo_default = 0;
        } else {
            $this->logo_default = 1;
        }

    }

    public function getTypeAsArray()
    {
        // Convert type to an array
        $this->type = $this->type !== null ? explode(',', $this->type) : [];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        // if a checkbox for type is selected
        if ($this->type !== '' ) {
            // transform the array of types (from request) to string
            $this->type = implode(",", $this->type);
        }
        return true;
    }

    public static function fetchSpaces($id)
    {

        if (!empty($id)) {
            // Fetch the space model
            $model = Spaces::findOne($id);

            if (!$model) {
                throw new \yii\web\NotFoundHttpException('The requested space does not exist.');
            }
            $model->setLogoDefault();
            $model->getTypeAsArray();

        } else {
            // Create a new space model
            $model = new Spaces();
            // preset only the not null default values
            $model->loadDefaultValues();
        }

        return $model;
    }

    public static function fetchSpacesBySuffix($url_suffix)
    {

        if (isset($url_suffix) && $url_suffix !== "") {
            $space_model = Spaces::find()->where(['url_suffix' => $url_suffix])->one();


            // if specified space model not found
            if (!$space_model)  {
                throw new \yii\web\NotFoundHttpException("Space Not Found");
            }
        } else {
            $space_model = New Spaces();
            // preset all the default values (null included)
            $space_model->loadDefaultNullValues();
        }

        return $space_model;
    }


    public function uploadLogo()
    {

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
        } else if ($this->logo_upload) {

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

    public static function fetchGetRequestArray($space_model, $post_request_array)
    {

        // the array that will be used for the GET redirection
        $get_request_array = [];

        // only the values that are different between the POST request and the current space model,
        // will appear in the GET request
        foreach ($space_model->toArray() as $key => $value) {
            if (array_key_exists($key, $post_request_array)) {
                // special handling for topics
                if ($key === 'topics' or $key === 'type') {
                    // sort arrays before comparison
                    $post_array = $post_request_array[$key];
                    sort($post_array);
                    sort($value);
                    if( $post_array !== $value){
                        if ($post_request_array[$key] === []){
                            // an empty array [] doesn't appear in the GET request, so we use [""] instead,
                            // we then remove it with array_filter(), after the request has been made
                            $get_request_array[$key] = [""];
                        } else {
                            $get_request_array[$key] = $post_request_array[$key];
                        }
                    }
                } elseif ($post_request_array[$key] !== $value) {
                    $get_request_array[$key] = $post_request_array[$key];
                }
            }
        }

        return $get_request_array;
    }

    public function loadDefaultNullValues()
    {
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

        // Convert type to an array
        $this->getTypeAsArray();

        // convert null->'' and integer->string
        $this->start_year = ($this->start_year === null) ? '' : strval($this->start_year);
        $this->end_year = ($this->end_year === null) ? '' : strval($this->end_year);

        return $this;
    }

    private static function enrichAnnotations($rows, $space_annotation) {
        
        // add annotation color, description
        foreach ($rows as $row => $row_data){
            $doi = $row_data[0];
            $annotations = $row_data[1];
            foreach ($annotations as $annotation_row => $annotation_data) {
                $rows[$row][1][$annotation_row]['annotation_id'] = $space_annotation['id'];
                $rows[$row][1][$annotation_row]['annotation_color'] = $space_annotation['color'];
                $rows[$row][1][$annotation_row]['annotation_description'] = $space_annotation['description'];
                $rows[$row][1][$annotation_row]['has_reverse_query'] = !empty($space_annotation['reverse_query']);
            }
        }
        
        return $rows;
    }

    public static function runAnnotationsQuery($dois = [], $annotation_db_name, $graph_db_system, $space_annotations) {

        $annotation_db = Yii::$app->params['annotation_dbs'][$annotation_db_name];

        $conn = GraphConnectionFactory::createConnection($graph_db_system, $annotation_db);
       
        // Test queries
        // $query = "MATCH (n) RETURN n";
        // $query = "MATCH (ee:Person) WHERE ee.name = 'Emil' RETURN ee.from";
        $query = "MATCH (p:Publication)-[r:HAS_EVIDENCE_ON]->(v:Variant) WHERE p.doi IN ['10.1126/science.aaa4967', '10.1086/342773'] RETURN DISTINCT (p.doi) AS `srcId`, COLLECT({ name: v.name, details: {description: r.description, assertion: r.assertion}}) AS `destData` LIMIT 25";
        $query_var = 'MATCH (p:Publication)-[r:HAS_EVIDENCE_ON]->(v:Variant) WHERE p.doi IN $dois RETURN DISTINCT (p.doi), COLLECT({ name: v.name, details: {description: r.description, assertion: r.assertion}}) LIMIT 25';
        // $stats = $protocol->run('RETURN $a AS num, $b AS str', ['a' => 123, 'b' => 'text']);
        // $stats = $protocol->run($query);
        // $stats = $protocol->run($query_var, ['dois' => ['10.1126/science.aaa4967', '10.1086/342773']]);
        // $stats = $protocol->run($query_var, ['dois' => $dois]);

        $data = [];
        foreach($space_annotations as $space_annotation) {

            [ $stats, $rows ] = $conn->run($space_annotation->query, ['dois' => $dois]);
            $data[] = self::enrichAnnotations($rows, $space_annotation);

        }

        return $data;

    }

    public static function fetchAnnotations($papers, $space_model) {

        $space_annotations = $space_model->annotations;
        if (empty($space_annotations)) {
            return $papers;
        }

        $dois = array_column($papers, 'doi');

        // give dois as query param
        // one array per annotation query
        $dois_to_annotations_db_multiple = self::runAnnotationsQuery($dois, $space_model->annotation_db, $space_model->graph_db_system, $space_annotations);

        // Group by doi per annotation
        $dois_to_annotations_multiple = [];
        foreach ($dois_to_annotations_db_multiple as $annotation_row => $dois_to_annotations_db){
            foreach ($dois_to_annotations_db as $row => $row_data){
                $doi = $row_data[0];
                $annotation_data = $row_data[1];
                $dois_to_annotations_multiple[$annotation_row][$doi] = $annotation_data;
            }
        }

        foreach ($papers as $paper => $paper_data){

            $doi = $paper_data['doi'];

            $annotations = [];
            foreach ($dois_to_annotations_multiple as $annotation_row => $dois_to_annotations){

                if (array_key_exists($doi, $dois_to_annotations)) {
                    $annotations = array_merge($annotations, $dois_to_annotations[$doi]);
                }
            }
            // Create annotation key to input array
            $papers[$paper]["annotations"] = $annotations;
        }

        return $papers;
    }

    public static function getSearchParams($space_url_suffix) {

        $space_model = Spaces::fetchSpacesBySuffix($space_url_suffix);
        $space_model->prepareForRequest();

        // all GET params
        $get_data_all = Yii::$app->request->get();
        // print_r($get_data_all);

        // merge the GET params into the space_model, to create the final SearchForm parameters
        $space_model->setAttributes($get_data_all, false);
        $search_params = $space_model->toArray();

        // revert topics, type set in fetchGetRequestArray ([""] -> []) 
        foreach (['topics', 'type'] as $key) {
            if (array_key_exists($key, $search_params) && $search_params[$key] === [""]) {
                $search_params[$key] = [];
            }
        }

        // add keywords 
        if (array_key_exists('keywords', $get_data_all)) {
            $search_params['keywords'] = $get_data_all['keywords'];
        } else {
            // when loading the page without search query
            $search_params['keywords'] = "";
        }

        // not used
        $search_params['location'] = (Yii::$app->request->get('location') == null || Yii::$app->request->get('location') == '') ? "title-abstract" : Yii::$app->request->get('location');

        return [
            $search_params,
            $space_model
        ];

    }

    public function getAnnotations()
    {
        return $this->hasMany(SpacesAnnotations::class, ['spaces_id' => 'id']);
    }

    /**
     * Custom validator to check that either both fields are empty or both are filled.
     */
    public function validateBothOrNone($attribute, $params, $validator)
    {
        if (($this->annotation_db && !$this->graph_db_system) || (!$this->annotation_db && $this->graph_db_system)) {
            $this->addError($attribute, 'Both "Annotation Database" and "Graph Database System" fields must be set.');
        }
    }
}