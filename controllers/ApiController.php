<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\SearchForm;
use app\models\User;
use app\models\Researcher;
use app\models\ResponsibleAcadAge;
use app\models\Scholar;
use app\models\ProteinDataBank;
use app\models\OpenaireArticle;

/**
 * This controller is used to partially serve the functionality of the API
 */
class ApiController extends Controller
{

    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                //    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionProfile($orcid = null) {

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!isset($orcid)) {
            throw new \yii\base\Exception("Field orcid cannot be empty");
        }

        $researcher = Researcher::findOne([ 'orcid' => $orcid ]);
        
        // if user with specified orcid not found or its profile is not public, throw not found
        if (!$researcher || !$researcher->is_public)  {
            throw new \yii\web\NotFoundHttpException("BIP! Scholar Profile Not Found");
        }

        $scholar = new Scholar($researcher);

        // fetch scholar's works for ORCiD
        $scholar->fetchWorks(null, null);

        $result = $scholar->getArticlesInPage([], [], [], [], [], 'year', null, null);

        // calculate and return scholar indicators
        $rag_data = ResponsibleAcadAge::get_responsible_academic_age_data($researcher->orcid);
        return $scholar->indicators->compute($rag_data);
    }

    public function actionSearch(
        $keywords = null,
        $ordering = "popularity",
        $start_year = 0,
        $end_year = 0,
        $popularity = "all",
        $influence = "all",
        $impulse = "all",
        $cc = "all",
        $page = 1,
        $page_size = 20,
        $auth_token = null,
        $rcsb_id = null
    )
    {

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$keywords) {
            throw new \yii\base\Exception("Field keywords cannot be empty");
        } else if (!$auth_token) {
            throw new \yii\base\Exception("Field auth_token cannot be empty");
        }

        if (!User::validateAuthToken($auth_token)) {
            throw new \yii\base\Exception("Please provide a valid auth_token");
        }

        $keywords = trim($keywords, " ");

        $location = "title-abstract";
        $relevance = "low";
        $journals = [];

        $model = new SearchForm($ordering, $keywords, $location, $relevance, $journals, $start_year, $end_year, $influence, $popularity, $impulse, $cc);

        $protein_primary_citation = null;
        if ($rcsb_id) {
            $protein_primary_citation = ProteinDataBank::findPrimaryCitation($rcsb_id);
        }

        $results = $model->searchLanguageForApi($protein_primary_citation, $page, $page_size);

        return $results;
    }

    public function actionImpactChart($id = null, $src = null){

        if (!isset($id)) {
            throw new \yii\base\Exception("Field id cannot be empty");
        }

        // provided id is not a DOI, check if it is valid openaire id
        if (str_starts_with($id, "10.")) {
            throw new \yii\base\Exception("Currently only OpenAIRE ids are supported, not DOIs");
        }

        $article = new OpenaireArticle($id);

        [ $code, $response ] = $article->get($src);
        if ($code == 404) {
            throw new \yii\web\NotFoundHttpException("Article with OpenAIRE id: " . $id . " was not found in the " . $src . " environment.");
        }

        $article->parse_response($response);

        $article->calculateChartData();

        return $this->renderAjax('impact_radar_chart', [
            'chart_data' => $article->chart_data,
        ]);
    }
}
