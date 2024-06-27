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
        "prod" => "https://services.openaire.eu/search/v2/api/results/",
        "beta" => "https://beta.services.openaire.eu/search/v2/api/results/",
    ];

    public function __construct($id){
        parent::__construct();
        $this->id = $id;
    }

    public function get($source) {

        if (empty($source)) {
            $source = "prod";
        }

        $url = OpenaireArticle::$api_endpoints[$source];

        $ch = curl_init($url . $this->id . '?format=json');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            $code,
            $response
        ];
    }

    private static function isMultidimensionalArray($field_value) {
        // check if is a multidimensional array: https://stackoverflow.com/a/994599/6938911
        return !(count($field_value) == count($field_value, COUNT_RECURSIVE));
    }

    public function parse_response($response) {

        $response = json_decode($response, true);

        if (!isset($response["result"]["metadata"]["oaf:entity"]["oaf:result"]))
            return [];

        $result = $response["result"]["metadata"]["oaf:entity"]["oaf:result"];

        if (isset($result["title"])) {
            if (OpenAIREArticle::isMultidimensionalArray($result["title"])) {
                $this->title = $result["title"][0]["content"];
            } else {
                $this->title = $result["title"]["content"];
            }
        }

        if (isset($result["description"])) {
            if (is_array($result["description"])) {
                $this->abstract = $result["description"][0];
            } else {
                $this->abstract = (empty($result["description"])) ? 'N/A' : $result["description"];
            }
        }

        // if there is one author, api returns one object, else it returns array; OpenAIRE's beautiful stuff :)
        if (isset($result["creator"])) {

            if (OpenAIREArticle::isMultidimensionalArray($result["creator"])) {

                // sort based on 'rank'
                usort($result["creator"], function($a, $b) { return $a['rank'] <=> $b['rank']; });

                // join with comma
                $this->authors = implode(", ", array_column($result["creator"], 'content'));
             } else {
                $this->authors = $result["creator"]["content"];
            }
        }

        if (isset($result["dateofacceptance"]))
            $this->year = substr($result["dateofacceptance"], 0, 4);


        if (isset($result["pid"])) {

            // $result["creator"] IS NOT a multidimensional array: https://stackoverflow.com/a/994599/6938911
            if (OpenAIREArticle::isMultidimensionalArray($result["pid"])) {
                $this->dois = array_filter(array_map(function($pid) { if ($pid['classid'] === 'doi') return $pid["content"]; }, $result["pid"]));
            } else {
                if ($result["pid"]["classid"] === "doi") {
                    $this->dois = [ $result["pid"]["content"] ];
                }
            }
        }

        // filter only impact measures (i.e., those that have id, score & class specified)
        // other measures also exist (e.g., views, downloads etc)
        $result["measure"] = array_filter($result["measure"], function($val) {
            return array_key_exists('id', $val) && array_key_exists('score', $val) && array_key_exists('class', $val);
        });

        // impact scores & classes
        if (isset($result["measure"]) && count($result["measure"]) > 0) {
            $this->measures = array_combine(array_column($result["measure"], 'id'), array_column($result["measure"], 'score'));
            $this->measures_classes = array_combine(array_column($result["measure"], 'id'), array_map(function($m) {
                // this function transforms classes from the old (3) to the new 5 ones.
                // TODO: adjust when classes in OpenAIRE are updated
                // A is the same
                if ($m == "A") return "A";
                if ($m == "B") return "C";
                else if ($m == "C") return "E";
                else return Yii::$app->params['impact_classes_mapping'][$m];

            }, array_column($result["measure"], 'class')));
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
        $this->chart_data = ChartData::calculate($impact["popularity"], $impact["influence"], $impact["impulse"], $impact["influence_alt"]);
    }
}