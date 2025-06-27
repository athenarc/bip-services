<?php

namespace app\models;

use Yii;
use yii\helpers\Url;
use yii\base\Model;
use app\models\ChartData;

/**
 * OpenaireArticle is the model used for fetching details for a specific id from OpenAIRE
 *
 */
class OpenaireArticle extends Model {

    public $id;
    public $title;
    public $authors;
    public $year;
    public $abstract;

    public $measures;
    public $measures_classes;

    public $dois = [];
    public $doi_papers;
    public $missing_dois;

    public $chart_data;

    private static $api_endpoints = [
        "prod" => "https://api.openaire.eu/graph/v2/researchProducts/",
        "beta" => "https://api-beta.openaire.eu/graph/v2/researchProducts/",
    ];

    public function __construct($id){
        parent::__construct();
        $this->id = $id;
    }

    public function get($source) {
        if (empty($source)) {
            $source = "prod";
        }

        // Use the correct Graph API base URL
        $url = OpenaireArticle::$api_endpoints[$source] . $this->id;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [$code, $response];
    }

    private static function isMultidimensionalArray($field_value) {
        // check if is a multidimensional array: https://stackoverflow.com/a/994599/6938911
        return !(count($field_value) == count($field_value, COUNT_RECURSIVE));
    }

   public function parse_response($response) {
        $response = json_decode($response, true);

        $impact = $response['indicators']['citationImpact'] ?? [];

        $map = [
            'citationCount' => 'citationClass',
            'popularity' => 'popularityClass',
            'influence' => 'influenceClass',
            'impulse' => 'impulseClass',
        ];

        // Mapping from OpenAIRE class to BIP chart class
        $classMap = [
            'C1' => 'A',
            'C2' => 'B',
            'C3' => 'C',
            'C4' => 'D',
            'C5' => 'E',
        ];

        $this->measures = [];
        $this->measures_classes = [];

        foreach ($map as $valueKey => $classKey) {
            if (isset($impact[$valueKey])) {
                $this->measures[$valueKey] = $impact[$valueKey];
            }
            if (isset($impact[$classKey])) {
                $rawClass = $impact[$classKey];
                if (isset($classMap[$rawClass])) {
                    $this->measures_classes[$valueKey] = $classMap[$rawClass];
                }
            }
        }
        // Remap citationCount -> citations (for compatibility)
        if (isset($this->measures['citationCount'])) {
            $this->measures['citations'] = $this->measures['citationCount'];
        }
        if (isset($this->measures_classes['citationCount'])) {
            $this->measures_classes['citations'] = $this->measures_classes['citationCount'];
        }
    }

    public function fetchPapersWithDOI($userid) {

        // user is not logged in
        if (!isset($userid)) {
            $userid = -1;
        }

        // fetch dois from db
        $this->doi_papers = (new \yii\db\Query())
            ->select('pmc_paper.*, user_id')
            ->from('pmc_paper')
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($userid) . ' AND showit = true')
            ->where(['doi' => $this->dois])
            ->groupBy('internal_id')
            ->all();

        // get impact scores
        $this->doi_papers = SearchForm::get_impact_class($this->doi_papers);

        // get concepts and scores
        $this->doi_papers = Concepts::getConcepts($this->doi_papers, 'internal_id');

        // get impact scores per concept
        $this->doi_papers = SearchForm::get_concepts_impact_class($this->doi_papers);

        // find missing papers, comparing $works received from orcid with papers found in our database
        $this->missing_dois = array_udiff($this->dois, $this->doi_papers, function($a, $b) {
             if (!isset($a))
                 return -1;
             elseif (!isset($b["doi"]))
                 return 1;
             else
                 return strcmp($a, $b["doi"]);
         });
    }

    public function calculateChartData() {
        $impact = $this->measures_classes;

        $fallback = 'E';

        $pop = $impact["popularity"] ?? $fallback;
        $inf = $impact["influence"] ?? $fallback;
        $imp = $impact["impulse"] ?? $fallback;
        $cit = $impact["citations"] ?? $fallback;

        $this->chart_data = ChartData::calculate($pop, $inf, $imp, $cit);
    }

}