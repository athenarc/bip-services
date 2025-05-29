<?php

namespace app\controllers;

use Yii;
use yii\base\Model;
use app\models\Spaces;
use app\models\SpacesAnnotations;
use app\models\Indicators;
use app\models\IndicatorsSearch;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\controllers\BaseController;
use app\models\Researcher;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\data\Pagination;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\ContactForm;
use app\models\SearchForm;
use app\models\SurveyForm;
use app\models\FeedbackForm;
use app\models\Article;
use app\models\Journal;
use app\models\DoiToPmc;
use app\models\UsersLikes;
use app\models\User;
use app\models\RequestresetForm;
use app\models\PassresetForm;
use app\models\PreviousUrlChecker;
use app\models\UserViews;
use app\models\GuestViews;
use app\models\AuthorPaperFetcher;
use app\models\PaperCitationHistories;
use app\models\SurveyPaperKeywords;
use app\models\SurveyCreditsForm;
use app\models\TagsToPapers;
use app\models\Concepts;
use app\models\Relations;
use app\models\CvNarrative;
use app\models\OpenaireArticle;
use app\models\AdminStats;
use app\models\ElementFacets;
use app\models\ElementIndicators;
use app\models\ElementIndicatorsForm;
use app\models\ElementNarratives;
use app\models\ElementNarrativesForm;
use app\models\ElementContributions;
use app\models\ElementDropdown;
use app\models\ElementDropdownOptions;
use app\models\ElementDividers;
use app\models\ElementDividersForm;
use app\models\ElementFacetsForm;
use app\models\ElementBulletedList;
use app\models\ElementTable;
use app\models\ElementTableHeaders;
use app\models\ProfileTemplateCategories;
use app\models\ProfileTemplateCategoriesSearch;
use app\models\Templates;
use app\models\TemplatesSearch;
use app\models\Elements;
use app\models\ElementsSearch;
use app\models\ElementNarrativesSearch;
use app\models\Facets;
use app\models\GraphConnectionFactory;
use app\models\Orcid;
use yii\web\Response;
use yii\widgets\ActiveForm;
use app\components\OrcidComponent;
use app\models\ChangePasswordForm;

class SiteController extends BaseController
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

    /**
     * Displays homepage.
     *
     * @return string
     *
     * @author Thanasis Vergoulis
     */
    public function actionIndex()
    {

        // POST request handling

        //If the post keyword parameter signifying a search has been set, we redirect to prettyUrl GET link
        if(Yii::$app->request->post('keywords') !== null) {

            // space
            $space_url_suffix = Yii::$app->request->post('space_url_suffix');

            $space_model = Spaces::fetchSpacesBySuffix($space_url_suffix);
            $space_model->prepareForRequest();

            // ordering, relevance, keywords
            $keywords = trim(Yii::$app->request->post('keywords'), " ");
            $ordering = Yii::$app->request->post('ordering');
            $user = User::findOne(Yii::$app->user->id);
            $relevance = ($user) ? $user->getKeywordRelevance() : "high";
            
            if($ordering == "year") {
                $relevance = "low";
            }

            $post_data_all = Yii::$app->request->post();
            $post_request_array= [
                'ordering' => $ordering,
                'relevance' => $relevance,
            ];

            // Filter values handling
            // check if the filters are present, by checking if one of them is present i.e popularity
            if (array_key_exists('popularity', $post_data_all)) {

                $clear_all = Yii::$app->request->post('clear_all');

                // if clear_all, all filter values will be taken from the current space model
                if (!$clear_all) {

                    // append the filter variables to post_request_array

                    // using array_values for reindexing after array_filter, to make possible the comparison with the space model
                    $post_request_array['topics'] = array_values(array_filter($post_data_all['topics']));
                    $post_request_array['start_year'] = $post_data_all['start_year'];
                    $post_request_array['end_year'] = $post_data_all['end_year'];
                    $post_request_array['popularity'] = $post_data_all['popularity'];
                    $post_request_array['influence'] = $post_data_all['influence'];
                    $post_request_array['impulse'] = $post_data_all['impulse'];
                    $post_request_array['cc'] = $post_data_all['cc'];
                    // if no checkbox is selected, type won't be present in post request
                    $post_request_array['type'] = $post_data_all['type'] ?? [];
                }
            }

            // create the array that will be used for the GET redirection.
            // contains values that appear in both post_request_array and space_model, and are different in the post request.
            $get_request_array = Spaces::fetchGetRequestArray($space_model, $post_request_array);

            // append keywords and space
            $get_request_array['keywords'] = $keywords;
            $get_request_array['space_url_suffix'] = $space_url_suffix;

            //Redirect to same action but with parameters in prettyURL format!
            return $this->redirect(Url::to(array_merge(['site/index'], $get_request_array)));

        }

        // GET request handling
        // cases:
        // - the search page loads from url
        // - redirection from a search keyword that gets submitted as a POST request
        
        [ $results, $search_model, $space_model ] = $this->doSearch();

        Url::remember();

        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        $keywords = Yii::$app->request->get('keywords');

        $researcherCount = 0;
        $researcherSearchUrl = null;

        if (!empty($keywords)) {
            $search_model_researcher = new \app\models\ScholarSearchForm($keywords, 'name');
            $scholarResults = $search_model_researcher->search();
            $researcherCount = count($scholarResults['rows']);

            if ($researcherCount > 0) {
                $researcherSearchUrl = Url::to([
                    '/scholar/search',
                    'keywords' => $keywords,
                    'ordering' => 'name'
                ]);
            }
        }

        return $this->render('index', [
            'model' => $search_model,
            'space_model' => $space_model,
            'results' => $results,
            'impact_indicators' => $impact_indicators,
            'researcherCount' => $researcherCount,
            'researcherSearchUrl' => $researcherSearchUrl,
            // 'author_list' => $author_list,
        ]);
    }

    public function doSearch() {

        // prepare search params and models
        [ $search_model, $space_model ] = $this->prepareSearchModels();

        // perform actual search
        $results = $search_model->search();

        return [ 
            $results,
            $search_model,
            $space_model,
        ];
    }

    private function prepareSearchModels() {

        $space_url_suffix = Yii::$app->request->get('space_url_suffix');

        [ $search_params, $space_model ] = Spaces::getSearchParams($space_url_suffix);

        //Initialise the form model
        $search_model = new SearchForm(
            $search_params['ordering'],
            $search_params['keywords'], 
            $search_params['location'], 
            $search_params['relevance'],
            $search_params['topics'], 
            $search_params['start_year'], 
            $search_params['end_year'], 
            $search_params['influence'],
            $search_params['popularity'], 
            $search_params['impulse'], 
            $search_params['cc'], 
            $search_params['type'], 
            $space_model
        );

        return [
            $search_model,
            $space_model,
        ];
    }

    public function actionGetTopTopics() {
    
        // prepare search params and models
        [ $search_model, $space_model ] = $this->prepareSearchModels();

        // perform facet search query
        $top_topics = $search_model->getTopicsFacet();

        // render top topics using partial view
        return $this->renderPartial('top_topics', [
            'top_topics' => $top_topics,
        ]);  
    
    }

    public function actionGetTopicEvolution() {

        $selected_topic = Yii::$app->request->get('selectedTopTopic');
        if (!$selected_topic) {
            throw new \yii\base\Exception("No topic is given");
        }

        [ $search_model, $space_model ] = $this->prepareSearchModels();

        [ $count_per_year, $citation_per_year ] = $search_model->getTopicEvolution($selected_topic);

        return $this->renderPartial('topic_evolution', [
            'count_per_year' => $count_per_year,
            'citation_per_year' => $citation_per_year,
        ]); 
    }

    /**
     * Displays the comparison page.
     *
     * @author Thanasis Vergoulis
     */
    public function actionComparison()
    {
        //Get cookies collection.
        $article_ids_str = $_COOKIE['bipComparison'];
        $article_ids = explode(",",$article_ids_str);

        $articles = [];

        $model = new SearchForm('','','');

        foreach($article_ids as $article_id) {

            if($article_id != "") {
                $article = $model->searchById($article_id);
                array_push($articles, $article);
            }
        }

        $articles = SearchForm::get_impact_class($articles);

        return $this->render('comparison', [
            'articles' => $articles,
        ]);
    }

    /**
     * Displays the details of a particular article.
     */
    public function actionDetails()
    {
        $id = Yii::$app->request->get('id');
        $space_url_suffix = Yii::$app->request->get('space_url_suffix');
        $space_model = Spaces::fetchSpacesBySuffix($space_url_suffix);

        // provided id is not a DOI, check if it is valid openaire id
        // if (!str_starts_with($id, "10.")) {
        //     $id = Yii::$app->request->get('id');
        //     $source = Yii::$app->request->get('src');
        //     $userid = Yii::$app->user->id;

        //     $article = new OpenaireArticle($id);

        //     [ $code, $response ] = $article->get($source);
        //     if ($code == 404) {
        //         throw new \yii\web\NotFoundHttpException("Article not found");
        //     }

        //     $article->parse_response($response);

        //     $article->fetchPapersWithDOI($userid);

        //     $article->calculateChartData();

        //     return $this->render('openaire', [
        //         'userid' => $userid,
        //         'article' => $article,
        //     ]);
        // }

        $doi = $id;

        $article = Article::find()->where(['doi' => $doi])->one();
        if (!$article) {
            throw new \yii\web\NotFoundHttpException("Article not found");
        }

        // properly format article details (authors, journal, abstract etc)
        $article->formatDetails();

        // Register a new user/guest view in database
        if (Yii::$app->user->isGuest) {
            Yii::$app->viewregister->registerGuestView(Yii::$app->getRequest()->getUserIP(), $article['internal_id']);
        } else {
            Yii::$app->viewregister->registerUserView(Yii::$app->user->id, $article['internal_id']);
        }

        // find if the paper has been liked
        $article_is_liked = $article->getUsersLikes()->where(['user_id' => Yii::$app->user->id, 'showit' => true])->exists();

        // add concepts
        [ $article ] = Concepts::getConcepts([ $article ], 'internal_id');

        // calculate total classes
        [ $article ] = SearchForm::get_impact_class([ $article ]);
        // calculate concepts classes
        [ $article ] = SearchForm::get_concepts_impact_class([ $article ]);
        // get relations
        [ $article ] = Relations::getRelations([ $article ]);

        // // Do not calculate pyramidStatistics for articles with NULL scores
        // if(isset($article['pagerank'])){
        //     // calculate statistics needed for pyramid charts
        //     $article->calculatePyramidStatisticsTotal();
        //     $article->calculatePyramidStatisticsJournal();

        //     // calculte journal classes
        //     $article->calculateJournalClasses();
        // }



        // calculate citation history
        // $history_dimensions = $article->calculateCitationHistory();

        // $topics = $article->getTopics();
        // $max_paper_views = Article::getMaxPaperViews();

        // $article->calculatePaperViews($article['internal_id'], $max_paper_views);
        // $article->caclulateLikes($article['internal_id']);

        // get readers statistics
        // $mendeley_access_token = Article::getMendeleyAccessToken();
        // $readers = Article::getMendeleyReaders($mendeley_access_token, $article["doi"]);
        // $article->setReaders($readers['reader_count']);

        // keep track of the max magnitude of readers
        // $readers_magnitude = Article::roundUpToNearestMagnitude($article->readers);

        // get paper reading status
        // $user_like:
        // -returns null if user is not logged in or if paper was never liked (showit: 0 or 1)
        // -returns reading_status value (0,1,2) otherwise
        $user_like = $article->getUsersLikes()->where(['user_id' => Yii::$app->user->id])->one();
        // if the article was never liked by user, initialize the value to 0
        $article_reading_status = (isset($user_like)) ? $user_like->reading_status : "0";

        $article->calculateChartData();

        //Render details page

        $indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->render('details', [
            'article' => $article,
            'liked' => $article_is_liked,
            'indicators' => $indicators,
            // 'xmin' => $history_dimensions['xmin'],
            // 'xmax' => $history_dimensions['xmax'],
            // 'ymax' => $history_dimensions['ymax'],
            // 'topics' => $topics,
            // 'readers' => $readers,
            // 'readers_magnitude' => $readers_magnitude,
            // 'total_views' => $max_paper_views,
            // 'total_likes' => UsersLikes::find()->count(),
            'article_reading_status' => $article_reading_status,
            'space_model' => $space_model,
        ]);
    }
    /**
     * Displays the articles of a particular annotation.
     */
    public function actionAnnotation()
    {
        $annotation_id = Yii::$app->request->get('annotation_id');
        $space_url_suffix = Yii::$app->request->get('space_url_suffix');
        $space_annotation_id = Yii::$app->request->get('space_annotation_id');
        $space_annotation = SpacesAnnotations::findOne(['id'=> $space_annotation_id]);

        $space_model = Spaces::fetchSpacesBySuffix($space_url_suffix);
        $annotation_db = Yii::$app->params['annotation_dbs'][$space_model->annotation_db];

        try {

            $conn = GraphConnectionFactory::createConnection($space_model->graph_db_system, $annotation_db);

            // Annotation Info
            [ $stats, $rows ] = $conn->run($space_annotation->reverse_query_info, ['annotation_id' => $annotation_id]);
            $annotation_info = $rows[0][0];

            // Annotation Dois Count
            [ $stats, $rows ] = $conn->run($space_annotation->reverse_query_count, ['annotation_id' => $annotation_id]);
            $dois_count = $rows[0][0];

            $pagination = new Pagination([
                'pageSize' => 10, 
                'totalCount' => $dois_count,
            ]);

            // Annotation Dois
            [ $stats, $rows ] = $conn->run($space_annotation->reverse_query, ['annotation_id' => $annotation_id, 'skip' => $pagination->offset, 'limit' => $pagination->limit]);
            $dois = array_map('strtolower', array_column(array_slice($rows, 0, -1), 0));
            
        } catch (\Exception $e) {
            throw new \yii\web\NotFoundHttpException('The requested annotation was not found');
        }

        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        $works = (new \yii\db\Query())
            ->select(['internal_id', 'dois_num', 'doi', 'title', 'authors', 'journal', 'year', 'type', 'is_oa', 'user_id', 'attrank', 'pagerank', '3y_cc', 'citation_count'])
            ->from('pmc_paper')
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where(['in', 'doi', $dois])
            ->orderBy([new \yii\db\Expression('FIELD(doi, ' . implode(',', array_map(function($element) { return "\"$element\""; }, $dois)) . ')')])
            ->all();

        // add the impact class of each row
        $works = SearchForm::get_impact_class($works);
        // get concepts and scores
        $works = Concepts::getConcepts($works, 'internal_id');
        // get impact scores per concept
        $works = SearchForm::get_concepts_impact_class($works);

        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->render('annotation_details', [
            'space_model' => $space_model,
            'annotation_info' => $annotation_info,
            'works' => $works,
            'pagination' => $pagination,
            'impact_indicators' => $impact_indicators,
        ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest)
        {
            return $this->goHome();
        }

        $model = new LoginForm();
        //Set message if we got here by POST method
        $model->postMsg = previousUrlChecker::msg_based_on_previous_url();

        if ($model->load(Yii::$app->request->post()) && $model->login())
        {
            return $this->goBack();
        }

        return $this->render('login', ['model' => $model]);
    }

    /*
     * Sign Up action
     */
    public function actionSignup() {
        /*
         * If we already are not guest, return to homepage
         */
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        //Create sign up form
        $model = new SignupForm();

        // load sign up model with post data
        $model_loaded = $model->load(Yii::$app->request->post());

        // when registering a new user WITH ORCID, 
        // generate a random password so that they can restore it later
        if ($model_loaded && !$model->password){ 
            $model->password = Yii::$app->security->generateRandomString(10);
        }

        if ($model_loaded && ($user = $model->signup())) {       

            // After signup is completed, log user in and go back
            $loginModel = new LoginForm();
            $loginModel->username = $model->username;
            $loginModel->password = $model->password;

            // Attempt to log the user in
            if ($loginModel->login()) {
                return $this->redirect(['site/index']);
            } else {
                // handle the case where login fails after signup
                Yii::$app->session->setFlash('error', 'Login failed after signup. Please contact the administrators.');
            }
        }

        //Render signup view
        return $this->render('signup', ['model' => $model]);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays the about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Displays the help page.
     *
     * @author Thanasis Vergoulis
     */
    public function actionHelp()
    {
        $indicators = Indicators::getImpactIndicatorsAsArray('Work');
        return $this->render('help', [
            'indicators' => $indicators
        ]);
    }

    /*
     * Send mail to user requesting password reset
     */
    public function actionSendmail()
    {
        $email = Yii::$app->request->get('email');
        $user_to_add_token = User::find()->where(['email' => $email])->one();
        $user_to_add_token->reset_key = Yii::$app->security->generateRandomString();
        $user_to_add_token->expires = new \yii\db\Expression('DATE_ADD(NOW(), INTERVAL 1 HOUR)');

        //Keep a copy of the token
        $token = $user_to_add_token->reset_key;
        $username = $user_to_add_token->username;

        //Update user with token
        if ($user_to_add_token->update() !== false)
        {
            //Now send user mail
            Yii::$app->mailer->compose('passwordReset', ['username' => $username,'token' => $token])
            ->setFrom([Yii::$app->params['adminEmail'] => 'Bip! Services'])
            ->setTo($email)
            ->setSubject('Password Reset on BIP! Services')
            ->send();
        }
        //All urls redirecting to login or password reset, should remember url
        Url::remember();
        return $this->redirect(['site/login']);
    }

    /*
     * Enter mail to receive pass reset code
     */
    public function actionRequestreset()
    {
        $model = new RequestresetForm();
        //Load possible post params and validate
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
            return $this->redirect(['site/sendmail', 'email' => $model->email]);
        }
        //Otherwise, render same page, with messages on model
        return $this->render('forgot', ['model' => $model]);
    }

    public function actionPassreset()
    {
        //Methods redirecting to login / password reset should remember urls
        Url::remember();
        $model = new PassresetForm();
        /*
         * token will be in get - the rest could be in post
         */
        $model->load(Yii::$app->request->get());
        $model->load(Yii::$app->request->post());

        //If model validates, we update the user and redirect to login
        if(Yii::$app->request->isPost)
        {
            if($model->validate())
            {
                    $current_user = User::find()->where(['username' => $model->username])->one();
                    $current_user->setPassword($model->newPass);
                    $current_user->reset_key = null;
                    $current_user->expires = null;
                    //Update user, set message

                    //If action succceeds go to login, otherwise display the same  (add to form the message)
                    if ($current_user->update())
                    {
                        $this->redirect(['site/successupdate']);
                    }
            }
        }

        $model->postMsg = ($model->hasErrors() || Yii::$app->request->isGet) ? '' : PreviousUrlChecker::msg_based_on_previous_url();
        //Return loaded form model if we didn't validate
        return $this->render('password_reset', ['model' => $model]);
    }

    /*
     * DEPRECATED: Run graphviz program
     */
    // public function actionGetgraph()
    // {
    //     //$pmc = Yii::$app->request->post("pmc");
    //     $pmc = Yii::$app->request->post('pmc');
    //     //$pmc = Yii::$app->request->post('pmc');

    //     //citing / cited depth
    //     $citing = (Yii::$app->request->post('citing') != null && Yii::$app->request->post('citing') != '') ? Yii::$app->request->post('citing') : 5;
    //     $cited  = (Yii::$app->request->post('cited') != null && Yii::$app->request->post('cited') != '') ? Yii::$app->request->post('cited') : 5;

    //     $metric = (Yii::$app->request->post('metric') != null && Yii::$app->request->post('metric') != '') ? Yii::$app->request->post('metric') : "popularity";
    //     $layout = (Yii::$app->request->post('layout') != null && Yii::$app->request->post('layout') != '') ? Yii::$app->request->post('layout') : 1;
    //     //Execute command
    //     exec("java -jar " . Yii::getAlias('@webroot') . "/../graph_stuff/GraphViz_medline_node.jar $pmc $cited $citing $metric $layout", $output_string, $return_val);
    //     $response = implode(" ", $output_string);

    //     return $this->renderPartial('ajax_graph', ['response' => $response]);
    // }

    /*
     * Ajax action for liking a paper
     */
    public function actionAjaxlike()
    {
        $user_id  = Yii::$app->user->id;
        $paper_id = Yii::$app->request->post('paper_id');
        $exists = UsersLikes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id])->exists();
        if(!$exists)
        {
            $user_like = new UsersLikes();
            $user_like->user_id = $user_id;
            $user_like->paper_id = $paper_id;
            $user_like->showit = true;
            $user_like->save();
        }
        /*
         * If like already exists, it may have showit set to FALSE.
         * In this case we need to update the field
         */
        else if(UsersLikes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id, 'showit' => false])->exists())
        {
            $user_like = UsersLikes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id, 'showit' => false])->one();
            $user_like->showit = true;
            $user_like->update();
        }
        else
        {
            // bookmark already exists with showit=>true
            throw new \yii\base\Exception;
        }
        return;
    }

    /*
     * Ajax action for un-liking a paper
     */
    public function actionAjaxunlike()
    {
        $user_id  = Yii::$app->user->id;
        $paper_id = Yii::$app->request->post('paper_id');
        $exists = UsersLikes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id, 'showit' => true])->exists();
        if($exists)
        {
            $user_like = UsersLikes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id, 'showit' => true])->one();
            $user_like->showit = false;
            $user_like->folder_id = null;
            $user_like->update();
        } else {
            // bookmark doesn't exist
            throw new \yii\base\Exception;
        }
        return;
    }

    public function actionAddTag() {
        $user_id  = Yii::$app->user->id;
        $tag_name = Yii::$app->request->post('tag_name');
        $paper_id = Yii::$app->request->post('paper_id');

        $tag_id = TagsToPapers::addTag($user_id, $paper_id, $tag_name);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'tag_id' => $tag_id
        ];
    }

    public function actionRemoveTag() {
        $user_id  = Yii::$app->user->id;
        $tag_name = Yii::$app->request->post('tag_name');
        $paper_id = Yii::$app->request->post('paper_id');

        $tag_id = TagsToPapers::removeTag($user_id, $paper_id, $tag_name);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'tag_id' => $tag_id
        ];
    }

    // /*
    //  * DEPRECATED: Ajax action to fetch paper title, year, possibly other data late and outlink!
    //  *
    //  * @author: Hlias
    //  */
    // public function actionPapersummary()
    // {
    //     $paper_id_in_number_format = Yii::$app->request->get('paper_id');
    //     $article = Article::find()->where(['pmc' => 'PMC' . $paper_id_in_number_format])->one();

    //     return $this->renderPartial('paper_summary', ['article' => $article]);
    // }

    /*
     * ##################################################################################################
     * Dummy redirection actions - Will be used when we send users to login, to find appropriate messages
     * ##################################################################################################
     */

    public function actionLikeunavailable()
    {
        Url::remember();
        $this->redirect(['site/login']);
    }

    public function actionSuccessupdate()
    {
        Url::remember();
        $this->redirect(['site/login']);
    }
    /*
     * ##################################################################################################
     */

    /*
     * Action to get autocomplete results for journal names in a magic search box
     */
    public function actionAutoCompleteJournals($expansion, $max_num=7, $term)
    {
        $journal_names = Journal::autocomplete($term, $max_num);
        if(empty($journal_names)) $journal_names = ["No suggestions found"];
        return json_encode($journal_names);
    }

    /*
     * Action to get autocomplete results for concept names in a magic search box
     */
    public function actionAutoCompleteConcepts($expansion, $max_num=7, $term)
    {
        $concepts = Concepts::autocomplete($term, $max_num);
        if(empty($concepts)) $concepts = ["No suggestions found"];
        return json_encode($concepts);
    }

    /*
     * Action to redirect to page that displays papers of particular authors
     */
    public function actionAuthor()
    {
        //IF post parameters are set, then continue with a redirect in order to get prettyURLs
        if(!empty(Yii::$app->request->post()))
        {
            $author = Yii::$app->request->post('author');
            $ordering = Yii::$app->request->post('ordering');
            return $this->redirect(Url::to(['site/author', 'ordering' => $ordering, 'author' => $author]));
        }

        //Get author keyword
        $author=Yii::$app->request->get('author');
        //Get ordering, if set
        $ordering = (Yii::$app->request->get('ordering') != '' && Yii::$app->request->get('ordering') != null) ? Yii::$app->request->get('ordering') : '';

        //Create author's paper fetcher
        $authors_paper_fetcher = new AuthorPaperFetcher($author, $ordering);
        //Get the number of distinct names found
        $distinct_names = $authors_paper_fetcher->synonym_author_list;

        //If there's a single author for that name, render his page
        if($authors_paper_fetcher->full_name_given || count($distinct_names)== 1 || count($distinct_names)== 0)
        {

            //Get author name & surname
            $author_full_keyword = $authors_paper_fetcher->author_kwd;

            //Don't need this line - it happens by default in the paper fetcher when we only have one author!
            //$author = $authors_paper_fetcher->format_author_name($author_full_keyword);

            //Get author's papers
            $author_papers = array();
            $author_papers_data = $authors_paper_fetcher->get_author_papers();
            $author_papers = $author_papers_data['author_papers'];
            $pagination = $author_papers_data['pagination'];
            $actual_author = $authors_paper_fetcher->author_kwd;
            $found_results = true;
            if(count($distinct_names) == 0)
            {
                $found_results = false;
            }
            return $this->render('authorpapers', ['author' => $actual_author, 'author_papers' => $author_papers, 'model' => $authors_paper_fetcher, 'ordering' => $ordering, 'pagination' => $pagination, 'found_results' => $found_results]);
        }
        //Redirect to name disambiguation in this case
        else
        {
            //Apply the author formatting to each of them
            $distinct_names = array_map(array($authors_paper_fetcher, 'format_author_name'), $distinct_names);
            //Sort the names in order for them to appear alphabetically
            sort($distinct_names, SORT_NATURAL);

            //Paginate
            $pagination = new Pagination(['totalCount'=>count($distinct_names)]);

            //Get statistics for authors found - ONLY in the currently examined range
            $author_stats = $authors_paper_fetcher->get_synonym_author_stats(array_slice($distinct_names, $pagination->offset, $pagination->limit));


            return $this->render('authordisambiguation', ['author' => $author, 'synonym_list' => array_slice($distinct_names, $pagination->offset, $pagination->limit), 'author_stats_array' => $author_stats, 'pagination' => $pagination]);
        }
    }

    // 	action used to render citations modal list via ajax
    public function actionGetCitations(){
        $paper_id = Yii::$app->request->get('paper_id');
        $citations = Article::getCitations($paper_id);
        $citations = SearchForm::get_impact_class($citations);
        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->renderPartial('papers_list', [
            'warning' => 'The citation list may be incomplete. This list contains all citations that BIP software was able to retrieve.',
            'papers' => $citations,
            'impact_indicators' => $impact_indicators
        ]);
    }

    public function actionGetReferences(){
        $paper_id = Yii::$app->request->get('paper_id');
        $references = Article::getReferences($paper_id);
        $references = SearchForm::get_impact_class($references); 
        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->renderPartial('papers_list', [
            'warning' => 'The reference list may be incomplete. This list contains all references that BIP software was able to retrieve.',
            'papers' => $references,
            'impact_indicators' => $impact_indicators
        ]);
    }

    public function actionGetVersions() {
        $openaire_id = Yii::$app->request->get('openaire_id');
        $versions = Article::getVersions($openaire_id);
        $versions = SearchForm::get_impact_class($versions); 
        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->renderPartial('papers_list', [
            'warning' => 'This list contains duplicate records, as identified by the <a href="https://graph.openaire.eu/docs/graph-production-workflow/deduplication" class="main-green" target="_blank">OpenAIRE deduplication algorithm</a> based on metadata analysis.',
            'papers' => $versions,
            'impact_indicators' => $impact_indicators
        ]);
    }

    public function actionGetRelationsData() {
        $source_openaire_id = Yii::$app->request->get('source_openaire_id');
        $target_dois = Yii::$app->request->get('target_dois');
        $relations = Article::getRelationsData($target_dois, $source_openaire_id);
        $relations = SearchForm::get_impact_class($relations); 
        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->renderPartial('papers_list', [
            'warning' => 'This section provides a list of relationships associated with the selected work.',
            'papers' => $relations,
            'impact_indicators' => $impact_indicators
        ]);
    }

    public function actionSaveSurveyCredits()
    {
        $model = new SurveyCreditsForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // do something meaningful here about $model ...
            // print_r($model->name);
            return $this->render('survey_end', ['credits_model' => $model]);
        }
    }

    public function actionDataPolicy(){
        return $this->render('data_policy');
    }

    /*
     * Page to opt out of Google Analytics
     *
     * @author: Kostis Zagganas (First Version: September 2018)
     */
    public function actionPrivacySettings(){
        $form_params =
        [
            'action' => URL::toRoute(['site/privacy-settings']),
            'options' =>
            [
                'class' => 'analytics_opt_out_form',
                'id'=> "analytics_form"
            ],
            'method' => 'GET'
        ];

        /*
         * Read cookie. If it exists, set checkbox value to false
         */
        $cookies = Yii::$app->request->cookies;

        if (isset($_COOKIE['bipAnalyticsOptOut']) )
        {
            $cookieValue=false;
        }
        else
        {
            $cookieValue=true;
        }

        /*
         * If form has submitted read the form field. If it hasn't use the cookie value.
         */
        $boxValue = isset($_GET['analytics_opt_out']) ? (boolean) $_GET['analytics_opt_out'] : $cookieValue;

        $boxLabel = $boxValue ? 'You are currently opted in. Click here to opt out.' : 'You are currently opted out. Click here to opt in.';

        $cookies = \Yii::$app->response->cookies;

        /*
         * If the form has submitted
         */
        if (isset($_GET['analytics_opt_out']))
        {
            /*
             * If the checkbox is set or the cookie does not exist delete cookie or else add a new cookie
             */
            if ($boxValue==true)
            {

                /*
                 * NOTE: For some weird reason, $cookie->remove does not work in Yii,
                 * so we have to do it manually with PHP.
                 */
                unset($_COOKIE['bipAnalyticsOptOut']);
                setcookie('bipAnalyticsOptOut', null, -1, '/');

            }
            else
            {
                /*
                 * NOTE: For some weird reason, the following commented code part does not work
                 */
                setcookie('bipAnalyticsOptOut', 1, 0, "/");
            }
            // print_r($cookies);

        }
        /*
         * Load the opt-out page
         */
        return $this->render('privacy_settings', ['form_params' => $form_params, 'boxValue' => $boxValue, 'boxLabel' => $boxLabel]);
    }

    public function actionAcceptCookies()
    {
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new \yii\web\Cookie([
                            'name' => 'BipCookiesAccept',
                            'value' => 'yes',
                            'expire' => 0,
                                            ]));
    }

    public function actionGetSimilarArticles() {
        $paper_id = Yii::$app->request->get('paper_id');
        $similar_papers = SearchForm::getSimilarArticles($paper_id);
        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->renderPartial('papers_list', [
            'papers' => $similar_papers,
            'impact_indicators' => $impact_indicators
        ]);
    }

    public function actionGetPapersInTopic() {
        $topic_id = Yii::$app->request->get('topic_id');

        $papers = Article::getArticlesWithTopic($topic_id);
        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->renderPartial('papers_list', [
            'papers' => $papers,
            'impact_indicators' => $impact_indicators
        ]);
    }

    public function actionDownloadBibtex() {
        $doi = Yii::$app->request->get('doi');
        $bibtex = Article::getBibtex($doi);

        // add new line before each comma
        $bibtex = str_replace(',', ',<br/><span class="tab"></span>', $bibtex);

        // if last char is '}', add a new line before it
        $last_char = substr($bibtex, -1);
        if ($last_char == "}") {
            $bibtex = substr($bibtex, 0, -1);
            $bibtex = $bibtex . "<br/>}";
        }
        return $bibtex;
    }

    public function actionGetPdfLink() {
        $doi = Yii::$app->request->get('doi');

        return Article::getPDFLink($doi);
    }

    public function actionPaperRankings() {
        $dois = Yii::$app->request->get('dois');
        $dois = explode(',', $dois);
        $papers = [];

        $papers = SearchForm::getReadings($dois);
        return json_encode($papers);
    }

    public function actionData() {
        // format articles in millions
        $articlesCount = floor(Article::find()->count() / 1000000);

        // format citations number in billions
        $citations = Article::getCitationsCount();
        $citationsCount = floor($citations / 1000000000);
        $citationsCount = $citationsCount . '.' . floor(($citations - 1000000000) / 1000000);

        return $this->render('data', [
            'articlesCount' => $articlesCount,
            'citationsCount' => $citationsCount
        ]);
    }

    public function actionIndicators() {
        $indicators = Indicators::find()->all();

        // Organize data into a nested array
        $organizedIndicators = [];
        $levels = [
            'Work' => 'Article-level Indicators',
            'Researcher' => 'Researcher-level Indicators'
        ];
        foreach ($indicators as $indicator) {
            if (isset($indicator->level) && isset($levels[$indicator->level])) {
                $category = $levels[$indicator->level];
            } else {
                $category = 'Unknown Level'; // Default value for unknown or missing levels
            }
            $family = $indicator->semantics.' Indicators';

            $organizedIndicators[$category][$family][$indicator->name] = [
                'Intuition' => $indicator->intuition,
                'Parameters' => $indicator->parameters,
                'Data & calculation' => $indicator->calculation,
                'Limitations' => $indicator->limitations,
                'Availability' => $indicator->availability,
                'Code' => $indicator->code,
                'References' => $indicator->references,
            ];

        }
        return $this->render('indicators', [
            'indicators' => $organizedIndicators,
        ]);
    }

    public function actionHome() {
        return $this->render('home');
    }

    public function actionAdminFlushAll() {
        // An action to flush the App cache.
        // When making changes to database tables the yii db cache should be flushed,
        // in order to immediately propagate those changes to active directory models

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        Yii::$app->cache->flush();

        return 'Cache Flushed';
    }

    public function actionAdminOverview() {

        $section = "overview";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $stats = new AdminStats();
        $stats->getStats();

        $monthly_user_data = AdminStats::getMonthlyUserData();
        $user_activity_data = AdminStats::getUserActivityData();


        return $this->render('admin/main', [
            'section' => $section,
            'overview_data' => [
                'stats' => $stats,
                'monthly_user_data' => $monthly_user_data,
                'user_activity_data' => $user_activity_data
            ],
        ]);
    }

    public function actionAdminSpaces() {
        $section = "spaces";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $space_id_update = Yii::$app->request->post("space_id_update");
        $model = Spaces::fetchSpaces($space_id_update);

        $modelsSpacesAnnotations = $model->isNewRecord ? [new SpacesAnnotations] : $model->annotations ;


        $spacesArray = ArrayHelper::map(Spaces::find()->all(), 'id', 'url_suffix');


        return $this->render('admin/main', [
            'section' => $section,
            'spaces_data' => [
                'model' => $model,
                'modelsSpacesAnnotations' => (empty($modelsSpacesAnnotations)) ? [new SpacesAnnotations] : $modelsSpacesAnnotations,
                'spacesArray' => $spacesArray
            ],
        ]);
    }

    public function actionAdminSaveSpaces() {
        $section = "spaces";


        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $current_space_id = Yii::$app->request->post("Spaces")['id'];
        $model = Spaces::fetchSpaces($current_space_id);

        $modelsSpacesAnnotations = $model->isNewRecord ? [new SpacesAnnotations] : $model->annotations ;

        # create new or update existing
        if ($model->load(Yii::$app->request->post())) {

            # before model validation
            $model->logo_upload = UploadedFile::getInstance($model, 'logo_upload');

            // Case: create
            if ($model->isNewRecord) {
                $modelsSpacesAnnotations = SpacesAnnotations::createMultipleModels(SpacesAnnotations::classname());
                Model::loadMultiple($modelsSpacesAnnotations, Yii::$app->request->post());

            // Case: update
            } else {
                $oldIDs = ArrayHelper::map($modelsSpacesAnnotations, 'id', 'id');
                $modelsSpacesAnnotations = SpacesAnnotations::createMultipleModels(SpacesAnnotations::classname(), $modelsSpacesAnnotations);
                Model::loadMultiple($modelsSpacesAnnotations, Yii::$app->request->post());
                $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelsSpacesAnnotations, 'id', 'id')));
            }

            // validate all models
            $valid1 = $model->validate();
            $valid2 = Model::validateMultiple($modelsSpacesAnnotations);

            if ($valid1 && $valid2){

                $model->uploadLogo();

                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // no need for 2nd validation
                    if ($flag = $model->save(false)) {
                        // Case: update
                        if (isset($deletedIDs) && !empty($deletedIDs)) {
                            SpacesAnnotations::deleteAll(['id' => $deletedIDs]);
                        }
                        foreach ($modelsSpacesAnnotations as $modelSpacesAnnotations) {
                            // give id, after $model is saved
                            $modelSpacesAnnotations->spaces_id = $model->id;
                            if (! ($flag = $modelSpacesAnnotations->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }
                    if ($flag) {
                        // Form is submitted and validation passes, save the spaces data
                        $transaction->commit();
                        return $this->redirect(['site/admin-spaces']);

                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                }

            }

        }

        # if model load, validate or save fails, return model with errors
        $spacesArray = ArrayHelper::map(Spaces::find()->all(), 'id', 'url_suffix');

        return $this->render('admin/main', [
            'section' => $section,
            'spaces_data' => [
                'model' => $model,
                'modelsSpacesAnnotations' => (empty($modelsSpacesAnnotations)) ? [new SpacesAnnotations] : $modelsSpacesAnnotations,
                'spacesArray' => $spacesArray
                    ],
        ]);

    }

    public function actionAdminDeleteSpaces() {


        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $space_id = Yii::$app->request->get("space_id");

        $model = Spaces::fetchSpaces($space_id);

        // logo exists
        if (isset($model->logo)) {
            // remove logo
            unlink($model->uploadLogoPath(true) . $model->logo);
        }
        // Spaces annotations will also get deleted (mysql ON DELETE CASCADE)
        $model->delete();

        return $this->redirect(['site/admin-spaces']);

    }

    public function actionSettings() {
        $user_id = Yii::$app->user->id;

        $user = User::findIdentity($user_id);

        // unlink orcid profile
        $unlink_profile = Yii::$app->request->get('unlink_profile');
        if ($unlink_profile == true) {

            if (isset($user->researcher)) {
                $user->researcher->delete();
            }

            $has_profile = false;

        } else {
            $has_profile = isset($user->researcher);
        }


        return $this->render('settings', [
            'user' => $user,
            'has_profile' => $has_profile,
            'unlink_profile' => $unlink_profile,
        ]);
    }

    public function actionAdminProfiles() {

        $section = "profiles";

        $searchTemplateCategoryModel = new ProfileTemplateCategoriesSearch();
        $templateCategoryDataProvider = $searchTemplateCategoryModel->search($this->request->queryParams);

        return $this->render('admin/main', [
            'section' => $section,
            'profiles_data' => [
                'profilesDataProvider' => $templateCategoryDataProvider,
            ]
        ]);
    }

    public function actionAdminIndicators() {

        $section = "indicators";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $searchIndicatorModel = new IndicatorsSearch();
        $indicatorDataProvider = $searchIndicatorModel->search($this->request->queryParams);

        return $this->render('admin/main', [
            'section' => $section,
            'indicators_data' => [
                'indicatorDataProvider' => $indicatorDataProvider
            ]
        ]);
    }

    /**
     * Displays a single Indicators model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewIndicator($id)
    {
        $section = "indicators";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        return $this->render('admin/indicators/view-indicator', [
            'section' => $section,
            'indicatorModel' => $this->findIndicatorModel($id),
        ]);
    }

    /**
     * Creates a new Indicators model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateIndicator()
    {
        $section = "indicators";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $indicatorModel = new Indicators();

        if ($this->request->isPost) {
            if ($indicatorModel->load($this->request->post()) && $indicatorModel->save()) {
                return $this->redirect(['view-indicator', 'id' => $indicatorModel->id]);
            }
        } else {
            $indicatorModel->loadDefaultValues();
        }

        return $this->render('admin/indicators/create-update-indicator', [
            'section' => $section,
            'indicatorModel' => $indicatorModel,
        ]);
    }

    /**
     * Updates an existing Indicators model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateIndicator($id)
    {
        $section = "indicators";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $indicatorModel = $this->findIndicatorModel($id);

        if ($this->request->isPost && $indicatorModel->load($this->request->post()) && $indicatorModel->save()) {
            return $this->redirect(['view-indicator', 'id' => $indicatorModel->id]);
        }

        return $this->render('admin/indicators/create-update-indicator', [
            'section' => $section,
            'indicatorModel' => $indicatorModel,
        ]);
    }

    /**
     * Deletes an existing Indicators model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteIndicator($id)
    {
        $section = "indicators";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $this->findIndicatorModel($id)->delete();

        return $this->redirect(['admin-indicators']);
    }

    /**
     * Finds the Indicators model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Indicators the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findIndicatorModel($id)
    {
        if (($indicatorModel = Indicators::findOne(['id' => $id])) !== null) {
            return $indicatorModel;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Displays a single ProfileTemplateCategories model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewTemplateCategory($id)
    {
        $section = "profiles";

        $searchTemplatesModel = new TemplatesSearch();
        $templateDataProvider = $searchTemplatesModel->search($this->request->queryParams);
        $templateDataProvider->query->andFilterWhere(['profile_template_category_id' => $this->findTemplateCategoryModel($id)]);
        return $this->render('admin/profiles/view-template-category', [
            'section' => $section,
            'templateCategoryModel' => $this->findTemplateCategoryModel($id),
            'templateDataProvider' => $templateDataProvider
        ]);
    }

    /**
     * Creates a new ProfileTemplateCategories model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateTemplateCategory()
    {
        $section = "profiles";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $templateCategoryModel = new ProfileTemplateCategories();

        if ($this->request->isPost) {
            if ($templateCategoryModel->load($this->request->post()) && $templateCategoryModel->save()) {
                return $this->redirect(['view-template-category', 'id' => $templateCategoryModel->id]);
            }
        } else {
            $templateCategoryModel->loadDefaultValues();
        }

        return $this->render('admin/profiles/create-update-template-category', [
            'section' => $section,
            'templateCategoryModel' => $templateCategoryModel,
        ]);
    }

    /**
     * Updates an existing ProfileTemplateCategories model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateTemplateCategory($id)
    {
        $section = "profiles";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $templateCategoryModel = $this->findTemplateCategoryModel($id);

        if ($this->request->isPost && $templateCategoryModel->load($this->request->post()) && $templateCategoryModel->save()) {
            return $this->redirect(['view-template-category', 'id' => $templateCategoryModel->id]);
        }

        return $this->render('admin/profiles/create-update-template-category', [
            'section' => $section,
            'templateCategoryModel' => $templateCategoryModel,
        ]);
    }

    /**
     * Deletes an existing ProfileTemplateCategories model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteTemplateCategory($id)
    {

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $this->findTemplateCategoryModel($id)->delete();

        return $this->redirect(['admin-profiles']);
    }

    /**
     * Finds the ProfileTemplateCategories model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return ProfileTemplateCategories the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findTemplateCategoryModel($id)
    {

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        if (($templateCategoryModel = ProfileTemplateCategories::findOne(['id' => $id])) !== null) {
            return $templateCategoryModel;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Displays a single Templates model.
     * @param int $id ID
     * @param int $profile_template_category_id Profile Template Category ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewTemplate($id, $profile_template_category_id)
    {
        $section = "profiles";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $searchElementsModel = new ElementsSearch();
        $elementsDataProvider = $searchElementsModel->search($this->request->queryParams);
        $elementsDataProvider->query->andFilterWhere(['template_id' => $id]);
        $elementsDataProvider->pagination = false;
        $elementsTotalUsers = ElementsSearch::findElementsUsers($id);

        $user_id = Yii::$app->user->id;
        $researcher = Researcher::findOne([ 'user_id' => $user_id ]);
        $templateModel = $this->findTemplateModel($id, $profile_template_category_id);

        // Generate the template URL if a researcher record is found
        $templateUrl = null;
        if ($researcher && $researcher->orcid && $templateModel) {
            $templateUrl = Yii::$app->urlManager->createAbsoluteUrl([
                'scholar/profile/' . $researcher->orcid . '/' . $templateModel->url_name,
            ]);
        }
       
        return $this->render('admin/profiles/view-template', [
            'section' => $section,
            'profile_template_category_id' => $profile_template_category_id,
            'templateModel' => $templateModel,
            'elementsDataProvider' => $elementsDataProvider,
            'elementsTotalUsers' => $elementsTotalUsers,
            'templateUrl' => $templateUrl,
        ]);
    }

    /**
     * Creates a new Templates model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateTemplate($profile_template_category_id)
    {
        $section = "profiles";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $templateModel = new Templates();

        if ($this->request->isPost) {
            if ($templateModel->load($this->request->post()) && $templateModel->save()) {
                // return $this->redirect(['view-template', 'id' => $templateModel->id, 'profile_template_category_id' => $templateModel->profile_template_category_id]);
                return $this->redirect(['update-template', 'id' => $templateModel->id,
                'profile_template_category_id' => $profile_template_category_id]);
            }
        } else {
            $templateModel->loadDefaultValues();
        }

        return $this->render('admin/profiles/create-update-template', [
            'section' => $section,
            'profile_template_category_id' => $profile_template_category_id,
            'templateModel' => $templateModel,
        ]);
    }

    /**
     * Updates an existing Templates model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @param int $profile_template_category_id Profile Template Category ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateTemplate($id, $profile_template_category_id)
    {
        $section = "profiles";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $searchElementsModel = new ElementsSearch();
        $elementsDataProvider = $searchElementsModel->search($this->request->queryParams);
        $elementsDataProvider->query->andFilterWhere(['template_id' => $id]);
        $elementsDataProvider->pagination = false;

        $templateModel = $this->findTemplateModel($id, $profile_template_category_id);

        if ($this->request->isPost && $templateModel->load($this->request->post()) && $templateModel->save()) {
            return $this->redirect(['view-template', 'id' => $templateModel->id, 'profile_template_category_id' => $templateModel->profile_template_category_id]);
        }

        return $this->render('admin/profiles/create-update-template', [
            'section' => $section,
            'profile_template_category_id' => $profile_template_category_id,
            'elementsDataProvider' => $elementsDataProvider,
            'templateModel' => $templateModel,
        ]);
    }

    /**
     * Deletes an existing Templates model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @param int $profile_template_category_id Profile Template Category ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteTemplate($id, $profile_template_category_id)
    {

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $this->findTemplateModel($id, $profile_template_category_id)->delete();

        return $this->redirect(['view-template-category', 'id' => $profile_template_category_id]);
    }

    /**
     * Finds the Templates model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @param int $profile_template_category_id Profile Template Category ID
     * @return Templates the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findTemplateModel($id, $profile_template_category_id)
    {
        if (($templateModel = Templates::findOne(['id' => $id, 'profile_template_category_id' => $profile_template_category_id])) !== null) {
            return $templateModel;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Displays a single Elements model.
     * @param int $id ID
     * @param int $template_id Template ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewElement($id, $template_id, $profile_template_category_id)
    {
        $section = "profiles";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $elementModel = $this->findElementModel($id, $template_id);
        $elementNarrativesModel = $elementModel->elementNarratives;
        $elementDividersModel = $elementModel->elementDividers;
        $elementContributionsModel = $elementModel->elementContributions;
        $elementDropdownModel = $elementModel->elementDropdown;
        $elementIndicatorsModel = $elementModel->elementIndicators;
        $elementFacetsModel = $elementModel->elementFacets;
        $elementBulletedListModel = $elementModel->elementBulletedList;
        $elementTableModel = $elementModel->elementTable;

        $all_indicators = Indicators::find()->orderBy(['level' => SORT_ASC, 'semantics' => SORT_ASC])->all();

        if ($elementIndicatorsModel) {
            foreach ($elementIndicatorsModel as $element_indicator) {
                $selected_indicators[] = [
                    'id' => $element_indicator->indicator->id,
                    'status' => $element_indicator->status
                ];
            }
        }
        else {
            $selected_indicators = [];
        }

        if ($elementFacetsModel) {
            foreach ($elementFacetsModel as $element_facet) {
                $selected_facets[] = [
                    'type' => $element_facet->facet->type,
                    'selected' => $element_facet->facet->selected,
                    'visualize_opt' => $element_facet->facet->visualize_opt,
                    'numbers_opt' => $element_facet->facet->numbers_opt,
                    'border_opt' => $element_facet->facet->border_opt,
                ];
            }
        }
        else {
            $selected_facets = [];
        }

        return $this->render('admin/profiles/view-element', [
            'section' => $section,
            'profile_template_category_id' => $profile_template_category_id,
            'elementModel' => $elementModel,
            'all_indicators' => $all_indicators,
            'elementNarrativesModel' => $elementNarrativesModel,
            'elementDividerModel' => $elementDividersModel,
            'elementContributionsModel' => $elementContributionsModel,
            'elementDropdownModel' => $elementDropdownModel,
            'elementBulletedListModel' => $elementBulletedListModel,
            'elementTableModel' => $elementTableModel,
            'selected_indicators' => $selected_indicators,
            'selected_facets' => $selected_facets,
        ]);
    }

    /**
     * Creates a new Elements model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateElement($template_id, $profile_template_category_id)
    {
        $section = "profiles";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $elementModel = new Elements();
        $indicatorList = Indicators::find()->orderBy(['level' => SORT_ASC, 'semantics' => SORT_ASC])->all();
        
        $elementIndicatorsFormModel = new ElementIndicatorsForm();
        $elementNarrativesFormModel = new ElementNarrativesForm();
        $elementDividersFormModel = new ElementDividersForm();
        $elementContributionsModel = new ElementContributions();
        $elementDropdownModel = new ElementDropdown();
        $elementDropdownOptionsModels = [new ElementDropdownOptions];
        $elementTableModel = new ElementTable();
        $elementTableHeadersModels = [new ElementTableHeaders];
        $elementFacetsFormModel = new ElementFacetsForm();
        $elementBulletedListModel = new ElementBulletedList();

        $semanticsOrder = ['Impact', 'Productivity', 'Open Science', 'Career Stage'];
        $indicatorOrder = [];

        if ($this->request->isPost) {
            if ($elementModel->load($this->request->post()) && $elementModel->save()) {

                $lastOrder = Elements::find()
                    ->select('MAX(`order`)')
                    ->where(['template_id' => $template_id])
                    ->scalar();

                $elementModel->order = $lastOrder ? $lastOrder + 1 : 1;

                switch ($elementModel->type) {
                    case 'Indicators':
                        if ($elementIndicatorsFormModel->load($this->request->post())) {

                            $semanticsOrder = $elementIndicatorsFormModel->semanticsOrder;
                            if (is_string($semanticsOrder)) {
                                $semanticsOrder = json_decode($semanticsOrder, true);
                            }
                            $semanticsOrder = is_array($semanticsOrder) ? array_map('strtolower', $semanticsOrder) : [];
                            $semanticsOrderIndex = array_flip($semanticsOrder);

                            $indicatorOrder = $elementIndicatorsFormModel->indicatorOrder;
                            if (is_string($indicatorOrder)) {
                                $indicatorOrder = json_decode($indicatorOrder, true);
                            }
                            $indicatorOrder = is_array($indicatorOrder) ? array_map('strtolower', $indicatorOrder) : [];
                            $indicatorOrderIndex = array_flip($indicatorOrder);

                            $selectedIndicators = $elementIndicatorsFormModel->selectedIndicators;

                            if (!empty($selectedIndicators)) {
                                foreach ($selectedIndicators as $indicatorId => $status) {
                                        $elementIndicators = new ElementIndicators();
                                        $elementIndicators->element_id = $elementModel->id;
                                        $elementIndicators->indicator_id = $indicatorId;
                                        $elementIndicators->status = $status;
                                        
                                        $semantics = strtolower($elementIndicators->indicator->semantics);
                                        $elementIndicators->semantics_order = isset($semanticsOrderIndex[$semantics]) ? $semanticsOrderIndex[$semantics] + 1 : null;
                                        $elementIndicators->indicator_order = isset($indicatorOrderIndex[$indicatorId]) ? $indicatorOrderIndex[$indicatorId] + 1 : null;
        
                                        $elementIndicators->save();
                                }
                            }
                        }
                        break;
                    case 'Narrative':
                        if ($elementNarrativesFormModel->load($this->request->post())) {
                            $elementNarrativesModel = new ElementNarratives();
                            $elementNarrativesModel->element_id = $elementModel->id;
                            $elementNarrativesModel->title = $elementNarrativesFormModel->title;
                            $elementNarrativesModel->heading_type = $elementNarrativesFormModel->heading_type;
                            $elementNarrativesModel->description = $elementNarrativesFormModel->description;
                            $elementNarrativesModel->hide_when_empty = $elementNarrativesFormModel->hide_when_empty;
                            $elementNarrativesModel->limit_value = $elementNarrativesFormModel->limit_value;
                            $elementNarrativesModel->limit_type = $elementNarrativesFormModel->limit_type;
                            $elementNarrativesModel->save();
                        }
                        break;
                    case 'Contributions List':
                        if ($elementContributionsModel->load($this->request->post())) {
                            $elementContributionsModel->element_id = $elementModel->id;
                            $elementContributionsModel->save();
                        }
                        break;
                    case 'Dropdown':
                        if ($elementDropdownModel->load($this->request->post())) {

                            $elementDropdownModel->element_id = $elementModel->id;

                            $elementDropdownOptionsModels = SpacesAnnotations::createMultipleModels(ElementDropdownOptions::classname());
                            Model::loadMultiple($elementDropdownOptionsModels, $this->request->post());
                
                            // validate all models
                            $valid1 = $elementDropdownModel->validate();
                            $valid2 = Model::validateMultiple($elementDropdownOptionsModels);
                            
                            if ($valid1 && $valid2) {

                                $transaction = \Yii::$app->db->beginTransaction();
                
                                try {
                                    if ($dropdownFlag = $elementDropdownModel->save(false)) {
                                        foreach ($elementDropdownOptionsModels as $elementDropdownOptionsModel) {
                                            $elementDropdownOptionsModel->element_dropdown_id = $elementDropdownModel->id;
                                            if (! ($dropdownFlag = $elementDropdownOptionsModel->save(false))) {
                                                $transaction->rollBack();
                                                break;
                                            }
                                        }
                                    }
                
                                    if ($dropdownFlag) {
                                        $transaction->commit();
                                    }
                                } catch (Exception $e) {
                                    $transaction->rollBack();
                                    $dropdownFlag = false;
                                }
                            }
                        }
                        break;
                    case 'Table':
                        if ($elementTableModel->load($this->request->post())) {

                            $elementTableModel->element_id = $elementModel->id;

                            $elementTableHeadersModels = SpacesAnnotations::createMultipleModels(ElementTableHeaders::classname());
                            Model::loadMultiple($elementTableHeadersModels, $this->request->post());
                
                            // validate all models
                            $valid1 = $elementTableModel->validate();
                            $valid2 = Model::validateMultiple($elementTableHeadersModels);
                            
                            if ($valid1 && $valid2) {

                                $transaction = \Yii::$app->db->beginTransaction();
                
                                try {
                                    if ($tableFlag = $elementTableModel->save(false)) {
                                        foreach ($elementTableHeadersModels as $elementTableHeadersModel) {
                                            $elementTableHeadersModel->element_table_id = $elementTableModel->id;
                                            if (! ($tableFlag = $elementTableHeadersModel->save(false))) {
                                                $transaction->rollBack();
                                                break;
                                            }
                                        }
                                    }
                
                                    if ($tableFlag) {
                                        $transaction->commit();
                                    }
                                } catch (Exception $e) {
                                    $transaction->rollBack();
                                    $tableFlag = false;
                                }
                            }
                        }
                        break;
                    case 'Section Divider':
                        if ($elementDividersFormModel->load($this->request->post())) {
                            $elementDividersModel = new ElementDividers();
                            $elementDividersModel->element_id = $elementModel->id;
                            $elementDividersModel->title = $elementDividersFormModel->title;
                            $elementDividersModel->heading_type = $elementDividersFormModel->heading_type;
                            $elementDividersModel->description = $elementDividersFormModel->description;
                            $elementDividersModel->show_description_tooltip = $elementDividersFormModel->show_description_tooltip;
                            $elementDividersModel->top_padding = $elementDividersFormModel->top_padding;
                            $elementDividersModel->bottom_padding = $elementDividersFormModel->bottom_padding;
                            $elementDividersModel->show_top_hr = $elementDividersFormModel->show_top_hr;
                            $elementDividersModel->show_bottom_hr = $elementDividersFormModel->show_bottom_hr;
                            $elementDividersModel->save();
                        }
                        break;
                    case 'Facets':
                        if ($elementFacetsFormModel->load($this->request->post())) {
                            $selectedFacets = $elementFacetsFormModel->selectedFacets;

                            $facets = [];

                            foreach ($selectedFacets as $selFacet) {
                                $parts = explode('-', $selFacet);
                                $facet_type = $parts[0];
                                $opts = $parts[1] ?? null;

                                if (!isset($facets[$facet_type])) {
                                    $facets[$facet_type] = [];
                                }

                                if ($opts !== null) {
                                    $facets[$facet_type][] = $opts;
                                }
                            }

                            foreach ($facets as $facet_type => $opts) {

                                $newFacet = new Facets();
                                $newFacet->selected = true;
                                $newFacet->type = $facet_type;

                                foreach ($opts as $opt) {
                                    switch ($opt) {
                                        case 'visualize_opt':
                                            $newFacet->visualize_opt = true;
                                            break;
                                        case 'numbers_opt':
                                            $newFacet->numbers_opt = true;
                                            break;
                                        case 'border_opt':
                                            $newFacet->border_opt = true;
                                            break;
                                    }
                                }

                                $newFacet->save();

                                $elementFacets = new ElementFacets();
                                $elementFacets->element_id = $elementModel->id;
                                $elementFacets->facet_id = $newFacet->id;
                                $elementFacets->save();
                            }
                        }
                        break;
                    case 'Bulleted List':
                        if ($elementBulletedListModel->load($this->request->post())) {
                            $elementBulletedListModel->element_id = $elementModel->id;
                            $elementBulletedListModel->save();
                        }
                        break;
                }

                // update elementModel->order
                if ($elementModel->save()) {
                    return $this->redirect(['update-template', 'id' => $elementModel->template_id,
                                            'profile_template_category_id' => $profile_template_category_id]);
                }
            }
        } else {
            $elementModel->loadDefaultValues();
            // load the default values from table schema, instead of default null initialization 
            $elementContributionsModel->loadDefaultValues();
            $elementBulletedListModel->loadDefaultValues();
        }

        return $this->render('admin/profiles/create-update-element', [
            'section' => $section,
            'template_id' => $template_id,
            'profile_template_category_id' => $profile_template_category_id,
            'indicatorList' => $indicatorList,
            'elementFacetsFormModel' => $elementFacetsFormModel,
            'elementIndicatorsFormModel' => $elementIndicatorsFormModel,
            'elementNarrativesFormModel' => $elementNarrativesFormModel,
            'elementDividersFormModel' => $elementDividersFormModel,
            'elementContributionsModel' => $elementContributionsModel,
            'elementDropdownModel' => $elementDropdownModel,
            'elementDropdownOptionsModels' => (empty($elementDropdownOptionsModels)) ? [new ElementDropdownOptions] : $elementDropdownOptionsModels,
            'elementTableModel' => $elementTableModel,
            'elementTableHeadersModels' => (empty($elementTableHeadersModels)) ? [new ElementTableHeaders] : $elementTableHeadersModels,
            'elementBulletedListModel' => $elementBulletedListModel,
            'elementModel' => $elementModel,
            'semanticsOrder' => $semanticsOrder,
            'indicatorOrder' => $indicatorOrder,
        ]);
    }

    /**
     * Updates an existing Elements model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @param int $template_id Template ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateElement($id, $template_id, $profile_template_category_id)
    {
        $section = "profiles";

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        $elementModel = $this->findElementModel($id, $template_id);
        $elementFacetsModel = $elementModel->elementFacets;
        $elementIndicatorsModel = $elementModel->elementIndicators;
        $elementNarrativesModel = $elementModel->elementNarratives;
        $elementDividersModel = $elementModel->elementDividers;
        $elementContributionsModel = $elementModel->elementContributions;
        $elementDropdownModel = $elementModel->elementDropdown;
        $elementDropdownOptionsModels = $elementDropdownModel->elementDropdownOptions ?? null;
        $elementBulletedListModel = $elementModel->elementBulletedList;
        $elementTableModel = $elementModel->elementTable;
        $elementTableHeadersModels = $elementTableModel->elementTableHeaders ?? null;
        $indicatorList = Indicators::find()->orderBy(['level' => SORT_ASC, 'semantics' => SORT_ASC])->all();
        $elementIndicatorsFormModel = new ElementIndicatorsForm();
        $elementNarrativesFormModel = new ElementNarrativesForm();
        $elementDividersFormModel = new ElementDividersForm();
        $elementFacetsFormModel = new ElementFacetsForm();

        $semanticsOrder = ['Impact', 'Productivity', 'Open Science', 'Career Stage'];
        $indicatorOrder = [];

        $existing_facets = [];
        if ($elementFacetsModel) {
            foreach ($elementFacetsModel as $element_facet) {
                $existing_facets[] = [
                    'type' => $element_facet->facet->type,
                    'selected' => $element_facet->facet->selected,
                    'visualize_opt' => $element_facet->facet->visualize_opt,
                    'numbers_opt' => $element_facet->facet->numbers_opt,
                    'border_opt' => $element_facet->facet->border_opt,
                ];
            }
        }

        $existing_indicators = [];
        if ($elementIndicatorsModel) {
            foreach ($elementIndicatorsModel as $element_indicator) {
                $existing_indicators[] = [
                    'id' => $element_indicator->indicator->id,
                    'status' => $element_indicator->status,
                    'semantics_order' => $element_indicator->semantics_order,
                    'semantics' => $element_indicator->indicator->semantics,
                    'indicator_order' => $element_indicator->indicator_order
                ];
            }
        }

        if ($this->request->isPost) {
            if ($elementModel->load($this->request->post()) && $elementModel->save()) {

                switch ($elementModel->type) {
                    case 'Indicators':
                        if ($elementIndicatorsFormModel->load($this->request->post())) {
                            $selectedIndicators = $elementIndicatorsFormModel->selectedIndicators;

                            $semanticsOrder = $elementIndicatorsFormModel->semanticsOrder;
                            if (is_string($semanticsOrder)) {
                                $semanticsOrder = json_decode($semanticsOrder, true);
                            }
                            $semanticsOrder = is_array($semanticsOrder) ? array_map('strtolower', $semanticsOrder) : [];
                            $semanticsOrderIndex = array_flip($semanticsOrder);
    
                            $indicatorOrder = $elementIndicatorsFormModel->indicatorOrder;
                            if (is_string($indicatorOrder)) {
                                $indicatorOrder = json_decode($indicatorOrder, true);
                            }
                            $indicatorOrder = is_array($indicatorOrder) ? array_map('strtolower', $indicatorOrder) : [];
                            $indicatorOrderIndex = array_flip($indicatorOrder);

                            ElementIndicators::deleteAll(['element_id' => $id]);
                            if (!empty($selectedIndicators)) {
                                foreach ($selectedIndicators as $indicatorId => $status) {
                                    $elementIndicators = new ElementIndicators();
                                    $elementIndicators->element_id = $id;
                                    $elementIndicators->indicator_id = $indicatorId;
                                    $elementIndicators->status = $status;

                                    $semantics = strtolower($elementIndicators->indicator->semantics);
                                    $elementIndicators->semantics_order = isset($semanticsOrderIndex[$semantics]) ? $semanticsOrderIndex[$semantics] + 1 : null;
                                    $elementIndicators->indicator_order = isset($indicatorOrderIndex[$indicatorId]) ? $indicatorOrderIndex[$indicatorId] + 1 : null;

                                    $elementIndicators->save();
                                }
                            }
                        }
                        break;
                    case 'Narrative':
                        if ($elementNarrativesFormModel->load($this->request->post())) {
                            $elementNarrativesModel->title = $elementNarrativesFormModel->title;
                            $elementNarrativesModel->heading_type = $elementNarrativesFormModel->heading_type;
                            $elementNarrativesModel->description = $elementNarrativesFormModel->description;
                            $elementNarrativesModel->hide_when_empty = $elementNarrativesFormModel->hide_when_empty;
                            $elementNarrativesModel->limit_value = $elementNarrativesFormModel->limit_value;
                            $elementNarrativesModel->limit_type = $elementNarrativesFormModel->limit_type;
                            $elementNarrativesModel->save();
                        }
                        break;
                    case 'Section Divider':
                        if ($elementDividersFormModel->load($this->request->post())) {
                            $elementDividersModel->title = $elementDividersFormModel->title;
                            $elementDividersModel->heading_type = $elementDividersFormModel->heading_type;
                            $elementDividersModel->description = $elementDividersFormModel->description;
                            $elementDividersModel->show_description_tooltip = $elementDividersFormModel->show_description_tooltip;
                            $elementDividersModel->top_padding = $elementDividersFormModel->top_padding;
                            $elementDividersModel->bottom_padding = $elementDividersFormModel->bottom_padding;
                            $elementDividersModel->show_top_hr = $elementDividersFormModel->show_top_hr;
                            $elementDividersModel->show_bottom_hr = $elementDividersFormModel->show_bottom_hr;
                            $elementDividersModel->save();
                        }
                        break;
                    case 'Contributions List':
                        if ($elementContributionsModel->load($this->request->post())) {
                            $elementContributionsModel->save();
                        }
                        break;
                    case 'Dropdown':
                        if ($elementDropdownModel->load($this->request->post())) {

                            $oldIDs = ArrayHelper::map($elementDropdownOptionsModels, 'id', 'id');
                            $elementDropdownOptionsModels = SpacesAnnotations::createMultipleModels(ElementDropdownOptions::classname(), $elementDropdownOptionsModels);
                            Model::loadMultiple($elementDropdownOptionsModels, Yii::$app->request->post());
                            $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($elementDropdownOptionsModels, 'id', 'id')));

                            // validate all models
                            $valid1 = $elementDropdownModel->validate();
                            $valid2 = Model::validateMultiple($elementDropdownOptionsModels);

                            if ($valid1 && $valid2) {

                                $transaction = \Yii::$app->db->beginTransaction();
                
                                try {
                                    if ($dropdownFlag = $elementDropdownModel->save(false)) {

                                        if (isset($deletedIDs) && !empty($deletedIDs)) {
                                            ElementDropdownOptions::deleteAll(['id' => $deletedIDs]);
                                        }
                                        foreach ($elementDropdownOptionsModels as $elementDropdownOptionsModel) {
                                            // give id, after elementDropdownModel is updated
                                            $elementDropdownOptionsModel->element_dropdown_id = $elementDropdownModel->id;
                                            if (! ($dropdownFlag = $elementDropdownOptionsModel->save(false))) {
                                                $transaction->rollBack();
                                                break;
                                            }
                                        }
                                    }
                
                                    if ($dropdownFlag) {
                                        $transaction->commit();
                                    }
                                } catch (Exception $e) {
                                    $transaction->rollBack();
                                    $dropdownFlag = false;
                                }
                            }
                        }
                        break;
                    case 'Table':
                        if ($elementTableModel->load($this->request->post())) {

                            $oldIDs = ArrayHelper::map($elementTableHeadersModels, 'id', 'id');
                            $elementTableHeadersModels = SpacesAnnotations::createMultipleModels(ElementTableHeaders::classname(), $elementTableHeadersModels);
                            Model::loadMultiple($elementTableHeadersModels, Yii::$app->request->post());
                            $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($elementTableHeadersModels, 'id', 'id')));

                            // validate all models
                            $valid1 = $elementTableModel->validate();
                            $valid2 = Model::validateMultiple($elementTableHeadersModels);

                            if ($valid1 && $valid2) {

                                $transaction = \Yii::$app->db->beginTransaction();
                
                                try {
                                    if ($tableFlag = $elementTableModel->save(false)) {

                                        if (isset($deletedIDs) && !empty($deletedIDs)) {
                                            ElementTableHeaders::deleteAll(['id' => $deletedIDs]);
                                        }
                                        foreach ($elementTableHeadersModels as $elementTableHeadersModel) {
                                            // give id, after elementTableModel is updated
                                            $elementTableHeadersModel->element_table_id = $elementTableModel->id;
                                            if (! ($tableFlag = $elementTableHeadersModel->save(false))) {
                                                $transaction->rollBack();
                                                break;
                                            }
                                        }
                                    }
                
                                    if ($tableFlag) {
                                        $transaction->commit();
                                    }
                                } catch (Exception $e) {
                                    $transaction->rollBack();
                                    $tableFlag = false;
                                }
                            }
                        }
                        break;
                    case 'Facets':
                        if ($elementFacetsFormModel->load($this->request->post())) {
                            $selectedFacets = $elementFacetsFormModel->selectedFacets;

                            $elementFacetsModel = $elementModel->elementFacets;

                            foreach ($elementFacetsModel as $element_facet) {
                                $facet = $element_facet->facet;

                                $facet->delete();
                            }

                            $facets = [];

                            foreach ($selectedFacets as $selFacet) {
                                $parts = explode('-', $selFacet);
                                $facet_type = $parts[0];
                                $opts = $parts[1] ?? null;

                                if (!isset($facets[$facet_type])) {
                                    $facets[$facet_type] = [];
                                }

                                if ($opts !== null) {
                                    $facets[$facet_type][] = $opts;
                                }
                            }

                            foreach ($facets as $facet_type => $opts) {

                                $newFacet = new Facets();
                                $newFacet->selected = true;
                                $newFacet->type = $facet_type;

                                foreach ($opts as $opt) {
                                    switch ($opt) {
                                        case 'visualize_opt':
                                            $newFacet->visualize_opt = true;
                                            break;
                                        case 'numbers_opt':
                                            $newFacet->numbers_opt = true;
                                            break;
                                        case 'border_opt':
                                            $newFacet->border_opt = true;
                                            break;
                                    }
                                }

                                $newFacet->save();

                                $elementFacets = new ElementFacets();
                                $elementFacets->element_id = $elementModel->id;
                                $elementFacets->facet_id = $newFacet->id;
                                $elementFacets->save();
                            }
                       }
                        break;
                    case 'Bulleted List':
                        if ($elementBulletedListModel->load($this->request->post())) {
                            $elementBulletedListModel->save();
                        }
                        break;
                }  
            }

            return $this->redirect(['update-template', 'id' => $template_id,
            'profile_template_category_id' => $profile_template_category_id]);
        }

        return $this->render('admin/profiles/create-update-element', [
            'section' => $section,
            'template_id' => $template_id,
            'profile_template_category_id' => $profile_template_category_id,
            'elementModel' => $elementModel,
            'elementIndicatorsModel' => $elementIndicatorsModel,
            'elementIndicatorsFormModel' => $elementIndicatorsFormModel,
            'elementNarrativesModel' => $elementNarrativesModel,
            'elementNarrativesFormModel' => $elementNarrativesFormModel,
            'elementDividersFormModel' => $elementDividersFormModel,
            'elementDividersModel' => $elementDividersModel,
            'elementContributionsModel' => $elementContributionsModel,
            'elementDropdownModel' => $elementDropdownModel,
            'elementDropdownOptionsModels' => (empty($elementDropdownOptionsModels)) ? [new ElementDropdownOptions] : $elementDropdownOptionsModels,
            'elementTableModel' => $elementTableModel,
            'elementTableHeadersModels' => (empty($elementTableHeadersModels)) ? [new ElementTableHeaders] : $elementTableHeadersModels,
            'elementFacetsFormModel' => $elementFacetsFormModel,
            'elementBulletedListModel' => $elementBulletedListModel,
            'indicatorList' => $indicatorList,
            'existing_indicators' => $existing_indicators,
            'existing_facets' => $existing_facets,
            'semanticsOrder' => $semanticsOrder,
            'indicatorOrder' => $indicatorOrder,
        ]);
    }

    /**
     * Deletes an existing Elements model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @param int $template_id Template ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteElement($id, $template_id, $profile_template_category_id)
    {
        $elementModel = $this->findElementModel($id, $template_id);

        if (!AdminStats::hasAdminAccess())  {
            throw new \yii\web\NotFoundHttpException("Page not Found");
        }

        if ($elementModel->type == "Facets") {
            $elementFacetsModel = $elementModel->elementFacets;

            foreach ($elementFacetsModel as $element_facet) {
                $facet = $element_facet->facet;
                $facet->delete();
            }
        }
        elseif ($elementModel->type == "Section Divider") {
            $elementDividersModel = $elementModel->elementDividers;
            $elementDividersModel->delete();
        }
        elseif ($elementModel->type == "Contributions List") {                    
            $elementContributionsModel = $elementModel->elementContributions;
            $elementContributionsModel->delete();
        }
        elseif ($elementModel->type == "Bulleted List") {                    
            $elementBulletedListModel = $elementModel->elementBulletedList;
            $elementBulletedListModel->delete();
        }

        // Relevant Element options (e.g., Contributions etc.) will be automatically deleted if the corresponding table has a foreign key with ON DELETE CASCADE set.
        $elementModel->delete();

        return $this->redirect(['update-template', 'id' => $template_id, 'profile_template_category_id' => $profile_template_category_id]);
    }

    /**
     * Finds the Elements model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @param int $template_id Template ID
     * @return Elements the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findElementModel($id, $template_id)
    {
        if (($model = Elements::findOne(['id' => $id, 'template_id' => $template_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpdateOrder()
    {
        if (Yii::$app->request->isAjax) {
            $order = Yii::$app->request->post('order');

            // Update order values in the database
            foreach ($order as $index => $id) {
                $model = Elements::findOne($id);
                if ($model) {
                    $model->order = $index + 1; // +1 because order starts from 1
                    $model->save(false); // Skip validation for simplicity
                }
            }

            // Optionally return a response
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false]);
    }

    /**
     * Finds the ElementIndicators model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $indicator_id Indicator ID
     * @param int $element_id Element ID
     * @return ElementIndicators the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findElementIndicatorsModel($indicator_id, $element_id)
    {
        if (($model = ElementIndicators::findOne(['indicator_id' => $indicator_id, 'element_id' => $element_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionOrcidLogin() {
        $redirect_uri = Url::to(['site/orcid-callback'], true);
        $authUrl = Orcid::getAuthorizationUrl($redirect_uri);
        return $this->redirect($authUrl);
    }

    public function actionOrcidCallback() {

        $redirect_uri = Url::to(['site/orcid-callback'], true);
        $orcid_data = Orcid::authorize(Yii::$app->request->get('code'), $redirect_uri);

        if ($orcid_data) {
 
            $auth_id = $orcid_data->orcid;
            $auth_provider = 'ORCID';

            $researcher = Researcher::findOne([ 'orcid' => $auth_id ]);

            $user = null;
            // if a researcher is found with the ORCID, log them in
            if ($researcher) {
                $user = $researcher->user;

            // else check if a user is registered (through orcid) with the same ORCID
            } else {
                $user = User::findOne([ 'auth_id' => $auth_id ]);
            }
            
            // no user is found, redirect to register user with orcid            
            if (!$user) {
                
                Yii::$app->session->set('auth_provider', 'ORCID');
                Yii::$app->session->set('auth_id', $auth_id);

                return $this->redirect(['site/signup']);
            }

            Yii::$app->user->login($user);

            return $this->redirect(['site/index']);
        }

        Yii::$app->session->setFlash('error', 'ORCID authentication failed.');
        return $this->redirect(['site/login']);
    }
    
    public function actionFeedback()
    {
        if (Yii::$app->user->isGuest) {
            throw new NotFoundHttpException('You must be logged in to submit feedback.');
        }

        $model = new FeedbackForm();
        $userEmail = Yii::$app->user->identity->email;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->mailer->compose()
                ->setTo(Yii::$app->params['adminEmail'])
                ->setCc($userEmail)
                ->setFrom([Yii::$app->params['adminEmail'] => 'Bip! Services'])
                ->setSubject("Feedback: {$model->category}")
                ->setTextBody("Subject: {$model->title}\n\nDescription: {$model->description}\n\nFrom: {$userEmail}")
                ->send();

            Yii::$app->session->setFlash('success', 'Your feedback has been submitted.');
            return $this->refresh();
        }

        return $this->render('feedback', ['model' => $model]);
    }

    public function actionChangePassword()
    {
        // if not logged in, redirect to login page
        $user = Yii::$app->user->identity;
        if (!$user) {
            return $this->redirect(['site/login']);
        }

        $model = new ChangePasswordForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->changePassword($user)) {
                Yii::$app->session->setFlash('success', 'Password changed successfully.');
                return $this->refresh();
            }
        }

        return $this->render('change_password', ['model' => $model]);
        
    }
}