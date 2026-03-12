<?php

namespace app\controllers;

use app\models\OpenaireArticle;
use app\models\Researcher;
use app\models\ResponsibleAcadAge;
use app\models\Scholar;
use app\models\SearchForm;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * This controller is used to partially serve the functionality of the API.
 */
class ApiController extends Controller {
    public $enableCsrfValidation = false;

    public function behaviors() {
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

    public function actions() {
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

        if (! isset($orcid)) {
            throw new \yii\base\Exception('Field orcid cannot be empty');
        }

        $researcher = Researcher::findOne(['orcid' => $orcid]);

        // if user with specified orcid not found or its profile is not public, throw not found
        if (! $researcher || ! $researcher->is_public) {
            throw new \yii\web\NotFoundHttpException('BIP! Scholar Profile Not Found');
        }

        $scholar = new Scholar($researcher);

        // fetch scholar's works for ORCiD
        $scholar->fetchWorksLimited(null, null);

        $result = $scholar->getArticlesInPage([], [], [], [], [], 'year', null, null);

        // calculate and return scholar indicators
        $rag_data = ResponsibleAcadAge::get_responsible_academic_age_data($researcher->orcid);

        return $scholar->indicators->compute($rag_data);
    }

    public function actionSearch(
        $keywords = null,
        $ordering = 'popularity',
        $start_year = 0,
        $end_year = 0,
        $popularity = 'all',
        $influence = 'all',
        $impulse = 'all',
        $cc = 'all',
        $page = 1,
        $page_size = 20,
        $auth_token = null
    ) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (! $keywords) {
            throw new \yii\base\Exception('Field keywords cannot be empty');
        } elseif (! $auth_token) {
            throw new \yii\base\Exception('Field auth_token cannot be empty');
        }

        if (! User::validateAuthToken($auth_token)) {
            throw new \yii\base\Exception('Please provide a valid auth_token');
        }

        $keywords = trim($keywords, ' ');

        $location = 'title-abstract';
        $relevance = 'low';
        $topics = [];

        // Use the same SearchForm logic as the main SiteController search
        $model = new SearchForm(
            $ordering,
            $keywords,
            $location,
            $relevance,
            $topics,
            $start_year,
            $end_year,
            $influence,
            $popularity,
            $impulse,
            $cc
        );

        // Run the full search pipeline (Solr + DB) used on the index page
        $results = $model->search();

        $pagination = $results['pagination'];
        $rows = $results['rows'];

        // Keep only the required fields per row
        $filteredRows = [];

        foreach ($rows as $row) {
            // Parse numeric metrics as numbers (DB returns them as strings)
            $attrank = isset($row['attrank']) ? (float) $row['attrank'] : null;
            $pagerank = isset($row['pagerank']) ? (float) $row['pagerank'] : null;
            $threeYcc = isset($row['3y_cc']) ? (int) $row['3y_cc'] : null;
            $citationCount = isset($row['citation_count']) ? (int) $row['citation_count'] : null;

            $filteredRows[] = [
                'id' => $row['internal_id'] ?? null,
                'doi' => $row['doi'] ?? null,
                'title' => $row['title'] ?? null,
                'abstract' => $row['abstract'] ?? null,
                'authors' => $row['authors'] ?? null,
                'journal' => $row['journal'] ?? null,
                'year' => $row['year'] ?? null,
                'attrank' => $attrank,
                'pagerank' => $pagerank,
                '3y_cc' => $threeYcc,
                'citation_count' => $citationCount,
                'pop_class' => $row['pop_class'] ?? null,
                'inf_class' => $row['inf_class'] ?? null,
                'imp_class' => $row['imp_class'] ?? null,
                'cc_class' => $row['cc_class'] ?? null,
            ];
        }

        return [
            'rows' => $filteredRows,
            'meta' => [
                'total_count' => (int) $pagination->totalCount,
                'page' => (int) $pagination->getPage() + 1,
                'page_size' => (int) $pagination->pageSize,
            ],
        ];
    }

    public function actionImpactChart($id = null, $src = null) {
        if (! isset($id)) {
            throw new \yii\base\Exception('Field id cannot be empty');
        }

        // provided id is not a DOI, check if it is valid openaire id
        if (str_starts_with($id, '10.')) {
            throw new \yii\base\Exception('Currently only OpenAIRE ids are supported, not DOIs');
        }

        $article = new OpenaireArticle($id);

        [ $code, $response ] = $article->get($src);

        if ($code == 404) {
            throw new \yii\web\NotFoundHttpException('Article with OpenAIRE id: ' . $id . ' was not found in the ' . $src . ' environment.');
        }

        $article->parse_response($response);

        $article->calculateChartData();

        return $this->renderAjax('impact_radar_chart', [
            'chart_data' => $article->chart_data,
        ]);
    }
}
