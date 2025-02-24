<?php

namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use app\models\SearchForm;
use app\models\UsersLikes;
use app\models\User;
use app\models\Researcher;
use app\models\UsersFolders;
use app\models\Orcid;
use app\models\Notes;
use app\models\Involvement;
use app\models\Scholar;
use app\models\ResponsibleAcadAge;
use app\models\CvNarrative;
use app\models\Indicators;
use app\models\ScholarIndicators;
use app\models\ScholarSearchForm;
use app\models\Templates;
use app\models\ElementNarratives;
use app\models\ElementNarrativeInstances;
use app\models\ProfileTemplateCategories;
use app\models\ElementIndicators;
use app\models\ElementFacets;
use app\models\ElementDividers;
use app\models\ElementContributions;
use app\models\ElementDropdown;
use app\models\ElementDropdownInstances;
use app\models\ElementBulletedList;
use app\models\ElementBulletedListItem;
use app\models\ElementTable;
use app\models\ElementTableInstances;
use app\components\common\CommonUtils;

class ScholarController extends Controller
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


    /*
     * Ajax action for updating paper involvement
     */
    public function actionAjaxinvolvement()
    {
        $user_id  = Yii::$app->user->id;
        $involvement_id = Yii::$app->request->post('involvement_id');
        $is_selected = Yii::$app->request->post('is_selected');
        $paper_id = Yii::$app->request->post('paper_id');

        Involvement::updateInvolvement($user_id, $paper_id, $involvement_id, $is_selected);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'involvement_name' => Yii::$app->params['involvement_fields'][$involvement_id],
        ];
    }

    /*
     * Ajax action for updating Responsible academic age (rag)
     * alias: Fair academic age
     */
    public function actionAddRag()
    {
        $user_id  = Yii::$app->user->id;
        if (!isset($user_id)) {
            Url::remember(['scholar/profile']);
            return $this->redirect(['site/login']);
        }
        
        $researcher = Researcher::findOne([ 'user_id' => $user_id]);
        $orcid = $researcher->orcid;
        $from_date = Yii::$app->request->post('from_date');
        $to_date = Yii::$app->request->post('to_date');
        $description = Yii::$app->request->post('description');

        $rag_response = ResponsibleAcadAge::updateRag($orcid, $from_date, $to_date, $description);
        $found_date_period = $rag_response["found"];
        $new_rag_data = $rag_response["saved_row"];

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'found_date_period' => $found_date_period,
            'new_rag_data' => $new_rag_data
        ];
    }
    /*
     * Ajax action for removing Responsible academic age (rag)
     * alias: Fair academic age
     */
    public function actionRemoveRag()
    {
        $user_id  = Yii::$app->user->id;
        if (!isset($user_id)) {
            Url::remember(['scholar/profile']);
            return $this->redirect(['site/login']);
        }

        $rag_id = Yii::$app->request->post('rag_id');

        ResponsibleAcadAge::removeRag($rag_id);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return;
    }
    /*
     * Ajax action for updating Responsible academic age html value(rag)
     * alias: Fair academic age
     */
    public function actionUpdateRag()
    {
        $user_id  = Yii::$app->user->id;
        if (!isset($user_id)) {
            Url::remember(['scholar/profile']);
            return $this->redirect(['site/login']);
        }
        $researcher = Researcher::findOne([ 'user_id' => $user_id ]);
        $orcid = $researcher->orcid;
        $min_year = Yii::$app->request->post('min_year');
        $academic_age = Yii::$app->request->post('academic_age');

        $rag_data = ResponsibleAcadAge::get_responsible_academic_age_data($orcid);
        $responsible_academic_age = ScholarIndicators::get_responsible_academic_age($academic_age, $rag_data, $min_year);

        $responsible_academic_age = (!isset($responsible_academic_age)) ? '-' : $responsible_academic_age;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return ['responsible_academic_age'=> $responsible_academic_age];
    }

    public function actionIndex() {

        $user_id = Yii::$app->user->id;
        $researcher = Researcher::findOne([ 'user_id' => $user_id ]);

        return $this->render('scholar_landing', [
            'researcher' => $researcher,
        ]);
    }

    public function actionMyprofile($template_url_name = null) {

        $user_id = Yii::$app->user->id;

        // redirect to login page, if not already logged in
        if (!isset($user_id)) {
            Url::remember();
            return $this->redirect(['site/login']);
        }

        $researcher = Researcher::findOne([ 'user_id' => $user_id ]);

        // append orcid if the user has scholar profile
        $redirect_url = 'scholar/profile/';
        if ($researcher) {
            $redirect_url .= $researcher->orcid;
        }

        // append template name if provided
        if (isset($template_url_name)) {
            $redirect_url .= '/' . $template_url_name;
        }

        return $this->redirect([$redirect_url]);
    }

    public function actionProfile($orcid = null, $template_url_name = null, $for_print = false/*, $cv_narrative_id = null*/) {

        $researcher = null;

        $current_cv_narrative = null;

        $template_url_name = isset($template_url_name) ? $template_url_name : Yii::$app->params['defaultTemplateUrlName'];

        $template = null;
        
        if (isset($orcid)) {
            $researcher = Researcher::findOne([ 'orcid' => $orcid ]);

            // if user with specified orcid not found or its profile is not public, throw not found
            if (!$researcher || (!$researcher->is_public && $researcher->user_id !== Yii::$app->user->id))  {
                throw new \yii\web\NotFoundHttpException("BIP! Scholar Profile Not Found");
            }

            // if (isset($cv_narrative_id)) {
            //     $current_cv_narrative = CvNarrative::findOne([ 'id' => $cv_narrative_id ]);

            //     if (!$template) {
            //         throw new \yii\web\NotFoundHttpException("BIP! Scholar Template Not Found");
            //     }
            // }
            
            // check if template is found and its visibility
            $template = Templates::findOne([ 'url_name' => $template_url_name ]);

            if(!$template){
                throw new \yii\web\NotFoundHttpException("BIP! Scholar Template Not Found");
            }
            
        } else {

            $user_id = Yii::$app->user->id;

            // redirect to login page, if not already logged in
            if (!isset($user_id)) {
                Url::remember();
                return $this->redirect(['site/login']);
            }

            // does not have {orcid} in path, redirect
            $researcher = Researcher::findOne([ 'user_id' => $user_id ]);
            if ($researcher) {
                return $this->redirect(['scholar/profile/' . $researcher->orcid]);
            }
        }

        // check if the request comes from cv-narrative modal
        // $is_cv_narrative_pjax = Yii::$app->request->get('_pjax') === "#cv-narrative-works-container";

        $edit_perm = isset($researcher) && ($researcher->user_id === Yii::$app->user->id);

        $auth_code = Yii::$app->request->get('code');

        $topics = Yii::$app->request->get('topics');
        $tags = Yii::$app->request->get('tags');
        $roles = Yii::$app->request->get('roles');
        $accesses = Yii::$app->request->get('accesses');

        // replace empty access with null, indicating unknown
        if (!empty($accesses)) {
            $accesses = array_map(function($r) { return ($r === '') ? null : $r; }, $accesses);
        }

        $types = Yii::$app->request->get('types');

        // if auth_code is present, user has requested to link account with orcid profile
        if (isset($auth_code) && !isset($researcher->access_token)) {
            $redirect_url = Url::to(['scholar/profile'], true);
            $response = Orcid::authorize($auth_code, $redirect_url);

            // researcher already exists (e.g. different users try to authorise with  the same ORCID account)
            $researcher_exists = Researcher::findOne([ 'orcid' => $response->orcid ]);
            if (!isset($researcher_exists)) {
                $researcher = Researcher::add($user_id, $response->orcid, $response->access_token, $response->name);
                return $this->redirect(['scholar/profile/' . $researcher->orcid]);
            }
        }

        $papers = [];
        $result = [
            "papers" => [],
            "papers_num" => 0,
        ];
        $indicators = [
            "work_types_num" => [
              "papers" => 0,
              "datasets" => 0,
              "software" => 0,
              "other" => 0
            ],
            "popular_works_count" => 0,
            "influential_works_count" => 0,
            "citations_num" => 0,
            "h_index" => 0,
            "i10_index" => 0,
            "popularity" => 0,
            "influence" => 0,
            "impulse" => 0,
            "openness" => '',
            "paper_min_year" => 0,
            "academic_age" => '',
            "responsible_academic_age" => ''
        ];
        $facets_selected = null;
        $rag_data = '';
        $missing_papers = [];
        $work_types_num = ['papers' => 0, 'datasets' => 0];
        // $cv_narrative_works = [];
        // $cv_narratives = [];
        // $public_cv_narratives_count = '';
        
        $template_elements = [];
        if ($template !== null && isset($template->elements)) {
            foreach($template->elements as $element) {

                $config = [];

                switch($element->type) {
                    case "Facets":
                        $config = ElementFacets::getConfigFacet($element->id);
                        break;

                    case "Indicators":
                        $config = ElementIndicators::getConfigIndicator($element->id);
                        break;

                    case "Contributions List":
                        $config = ElementContributions::getConfigContributions($element->id);
                        // sort parameter from Contributions List config
                        $sort_config = $config["sort"] ?? null;
                        $show_pagination_config = $config["show_pagination"] ?? null;
                        $top_k_config = $config["top_k"] ?? null;
                        $page_size_config = $config["page_size"] ?? null;
                        break;

                    case "Section Divider":
                        $config = ElementDividers::getConfigDivider($element->id);
                        break;

                    case "Dropdown":
                        $config = ElementDropdown::getConfigDropdown($element->id, $template->id, $researcher->user_id);
                        break;

                    case "Narrative":
                        $config = ElementNarratives::getConfigNarrative($element->id, $template->id, $researcher->user_id);
                        $clean_text = CommonUtils::cleanText($config->value);
                        $text_value = 0;
                        $limit_status = null;
                        if ($config->limit_value) {
                            if ($config->limit_type == ElementNarratives::TYPE_WORDS) {
                                $text_value = str_word_count($clean_text);
                            } elseif ($config->limit_type == ElementNarratives::TYPE_CHARACTERS) {
                                $text_value = mb_strlen($clean_text);
                            }
                        }
                        if ($config->limit_value && $text_value > $config->limit_value) {
                            $limit_status = "Your text is over the limit of {$config->limit_value} {$config->getLimitTypeName()}. Text that exceeds this limit is not displayed in the public profile page.";
                        }
                        if (!$edit_perm) {
                            if ($text_value > $config->limit_value) {
                                if  ($config->hide_when_empty) {
                                    unset($config);
                                }
                                else {
                                    $config->value = '<div class="alert alert-warning text-center" role="alert">This narrative is not displayed since it exceeds the limit set by the template.</div>';
                                }
                            }
                        }
                        break;
                    case 'Bulleted List':
                        $config = ElementBulletedList::getConfig($element->id, $template->id, $researcher->user_id);
                        break;

                    case "Table":
                        $config = ElementTable::getConfigTable($element->id, $template->id, $researcher->user_id);
                        break;
                    default:
                        throw new \yii\base\Exception("Unknown element type: " . $element->type);                    
                }

                // add config for new element
                if (isset($config)) {
                    $template_elements[] = [
                        'element_id' => $element->id,
                        'type' => $element->type,
                        'name' => $element->name,
                        'config' => $config,
                        'limit_status' => isset($limit_status) ? $limit_status : ''
                    ];
                }
            }
        }

        // Determine the sort field:
        // - If $top_k is set, ignore the 'sort' GET parameter and default to $sort_config or 'year'.
        // - If $top_k is not set, check the 'sort' GET parameter first, falling back to $sort_config or 'year' if not provided.
        $sort_field = isset($top_k_config) 
            ? ($sort_config ?? 'year') 
            : Yii::$app->request->get('sort', $sort_config ?? 'year');

        // Initialize configs to avoid php notice, when Contribution Element not present in current template
        $top_k_config = $top_k_config ?? null;
        $show_pagination_config = $show_pagination_config ?? null;
        $page_size_config = $page_size_config ?? null;


        if(isset($researcher->access_token)) {

            $scholar = new Scholar($researcher);

            // fetch scholar's works for ORCiD
            $scholar->fetchWorks($sort_field, $top_k_config);

            // avoid calculation of redundant information, when the request is not coming from cv-narratives modal.
            // proper modifications were made in the profile view also.
            // if(!$is_cv_narrative_pjax){

            //     if (isset($current_cv_narrative)) {

            //         $cv_narrative_paper_ids = explode(',', $current_cv_narrative->papers);
            //         $scholar->fetchCvNarrativeDois($cv_narrative_paper_ids);
            //         $topics = $tags = $roles = $accesses = $types = null;

            //     }

            // fetch papers in current page
            $result = $scholar->getArticlesInPage($topics, $tags, $roles, $accesses, $types, $sort_field, $show_pagination_config, $page_size_config);

            // true if at least a facet is selected
            $facets_selected = !empty($tags) || !empty($accesses) || !empty($rd_status) || !empty($types) || !empty($topics);

            // get last selected facet field and its value
            $facet_field = Yii::$app->request->get('fct_field');

            $result["facets"] = $scholar->getFacets($topics, $tags, $roles, $accesses, $types, $facet_field);

            // fetch involvement
            $result = Involvement::getInvolvement($result, $researcher->user_id);

            $missing_papers = $scholar->missing_papers;

            // calculate scholar indicators
            $rag_data = ResponsibleAcadAge::get_responsible_academic_age_data($researcher->orcid);

            $indicators = $scholar->indicators->compute($rag_data);

            // find all cv narratives of the user
            // $cv_narratives = CvNarrative::find()->where([ 'user_id' => $researcher->user_id ])->all();

            // $public_cv_narratives_count = CvNarrative::CountPublicCvNarratives($cv_narratives);
            // }

            // if($edit_perm) {

            //     // compute the works data for CV narrative creation
            //     $cv_narrative_works = new ArrayDataProvider([
            //         'allModels' => $scholar->getOnlyAllArticlesInPage(),
            //         'pagination' => [
            //             'pageSize' => 30,
            //             'pageParam' => 'cv-narrative-page',
            //             'pageSizeParam' => 'cv-narrative-per-page',
            //         ],
            //         'sort' => [
            //             'attributes' => ['year', 'title'],
            //             'sortParam' => 'cv-narrative-sort',
            //             'defaultOrder' => ['year' => SORT_DESC]
            //         ],
            //     ]);

            // }
        }

        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');
        $data = [
            'impact_indicators' => $impact_indicators,
            'researcher' => $researcher,
            'researcher_exists' => $researcher_exists ?? null,
            'edit_perm' => $edit_perm,
            'result' => $result,
            'papers_num' => $indicators['work_types_num']['papers'],
            'datasets_num' => $indicators['work_types_num']['datasets'],
            'software_num' => $indicators['work_types_num']['software'],
            'other_num' => $indicators['work_types_num']['other'],
            'citations' => $indicators['citations_num'],
            'h_index' => $indicators['h_index'],
            'i10_index' => $indicators['i10_index'],
            'popular_works_count' => $indicators['popular_works_count'],
            'influential_works_count' => $indicators['influential_works_count'],
            'popularity' => $indicators['popularity'],
            'influence' => $indicators['influence'],
            'impulse' => $indicators['impulse'],
            'openness' => $indicators['openness'],
            'academic_age' => $indicators['academic_age'],
            'paper_min_year' => $indicators['paper_min_year'],
            'responsible_academic_age' => $indicators['responsible_academic_age'],
            'rag_data' => $rag_data,
            'missing_papers' => $missing_papers,
            'highlight_key' => 'Profile',
            'orderings' => [
                'year' => 'Publication year',
                'influence' => 'Influence',
                'popularity' => 'Popularity',
                'impulse' => 'Impulse',
                'citation_count' => 'Citation Count'
            ],
            'selected_topics' => $topics,
            'selected_tags' => $tags,
            'selected_roles' => $roles,
            'selected_accesses' => $accesses,
            'selected_types' => $types,
            'facets_selected' => $facets_selected,
            'sort_field' => $sort_field,
            // 'is_cv_narrative_pjax' => $is_cv_narrative_pjax,
            // 'cv_narrative_works' => $cv_narrative_works,
            // 'cv_narratives' => $cv_narratives,
            // 'current_cv_narrative' => $current_cv_narrative,
            // 'public_cv_narratives_count' => $public_cv_narratives_count,

            'template_elements' => $template_elements,
            'template' => $template,
            'templateDropdownData' => ProfileTemplateCategories::getTemplateDropdownData(),
            'template_url_name' => $template_url_name,
        ];

        if ($for_print) {
            return $data;
        }
        return $this->render('profile', $data);
    }

    // used to serve profile indicators to the API
    public function actionProfileIndicators($orcid = null) {

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!isset($orcid)) {
            return [];
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


    public function actionAjaxUpdatePublicProfile() {

        $is_public = Yii::$app->request->post('is_public');
        $user_id = Yii::$app->user->id;

        $researcher = Researcher::updatePublicProfile($user_id, $is_public);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'orcid' => $researcher->orcid,
        ];
    }

    public function actionSaveCvNarrative() {

        $user_id = Yii::$app->user->id;
        // redirect to login page, if not already logged in
        if (!isset($user_id)) {
            Url::remember();
            return $this->redirect(['site/login']);
        }

        $cv_narrative_id = Yii::$app->request->post('new_cv_narrative_id');

        if (!isset($cv_narrative_id)) {
            // insert new narrative
            $cv_narrative = new CvNarrative();
            $cv_narrative->user_id = $user_id;

        } else {
            // update existing narrative
            $cv_narrative = CvNarrative::find()->where(['id' => $cv_narrative_id])->one();
            if (!$cv_narrative) {
                throw new \yii\base\Exception;
            }
        }

        $cv_narrative->title = Yii::$app->request->post('new_cv_narrative_title');
        $cv_narrative->description = Yii::$app->request->post('new_cv_narrative_description');
        $cv_narrative->papers = Yii::$app->request->post('new_cv_narrative_selected_papers');
        // updates or saves the given record, no need for ->update()
        $cv_narrative->save();

        $researcher = Researcher::findOne([ 'user_id' => $user_id ]);

        return $this->redirect(['scholar/profile/' . $researcher->orcid . '/' .  $cv_narrative->id]);
    }

    public function actionDeleteCvNarrative() {

        $selected_cv_narrative_id = Yii::$app->request->get('selected_cv_narrative_id');
        $user_id = Yii::$app->user->id;

        // redirect to login page, if not already logged in
        if (!isset($user_id)) {
            Url::remember();
            return $this->redirect(['site/login']);
        }

        $found_cv_narrative = CvNarrative::find()->where(['id' => $selected_cv_narrative_id])->one();
        if (!empty($found_cv_narrative) && ($found_cv_narrative->user_id === $user_id) ) {
            $found_cv_narrative->delete();
        } else {
            throw new \yii\web\NotFoundHttpException("CV narrative not found.");
        }

        return $this->redirect(['scholar/profile']);
    }

    public function actionSearch($keywords = null, $ordering = null) {

        $search_model = new ScholarSearchForm($keywords, $ordering);
        $results = null;

        if (isset($keywords)) {
            $results = $search_model->search();
        }
        
        return $this->render('search', [
            'search_model' => $search_model,
            'results' => $results
        ]);
    }

    public function actionSaveNarrativeInstance() {
        $template_id = Yii::$app->request->post('template_id');
        $element_id = Yii::$app->request->post('element_id');
        $element_text = Yii::$app->request->post('value');
        $user_id  = Yii::$app->user->id;

        if (!isset($template_id) || !isset($element_id) || !isset($element_text) || !isset($user_id)) {
            throw new \yii\base\Exception;
        }

        $instance = ElementNarrativeInstances::findOne([
            'template_id' => $template_id,
            'element_id' => $element_id,
            'user_id' => $user_id
        ]);
        
        if (!$instance) {
            // Create a new instance
            $instance = new ElementNarrativeInstances();
            $instance->template_id = $template_id;
            $instance->element_id = $element_id;
            $instance->user_id = $user_id;
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (empty($element_text)) {
            // Delete the instance
            if (!$instance->delete()) {
                // Error occurred while deleting
                throw new \yii\base\Exception("Error deleting record: " . implode(", ", $instance->getFirstErrors()));
            }
            return [
                "message" => null,
                "count" => null,
                "limit_status" => null
            ];
        
        } else {
            $clean_text = CommonUtils::cleanText($element_text);
            $instance->value = $element_text;
            
            // Calculate word/character limit
            $element_narrative = ElementNarratives::findOne(['element_id' => $element_id]);
            $text_value = 0;
            $limit_status = null;
            $limit_type = $element_narrative->limit_type ?? null;
            $limit_value = $element_narrative->limit_value ?? null;
    

            if ($limit_type == ElementNarratives::TYPE_WORDS) {
                $text_value = str_word_count($clean_text);
            } elseif ($limit_type == ElementNarratives::TYPE_CHARACTERS) {
                $text_value = mb_strlen($clean_text);
            }
    
            if ($limit_value && $text_value > $limit_value) {
                $limit_status = "Your text is over the limit of {$limit_value} {$element_narrative->getLimitTypeName()}. Text that exceeds this limit is not displayed in the public profile page.";
            }

    
            // Save the instance
            if (!$instance->save()) {
                // Error occurred while saving
                throw new \yii\base\Exception("Error saving record: " . implode(", ", $instance->getFirstErrors()));
            }

            $message = CommonUtils::timeSinceUpdate($instance->last_updated);

            return [
                "message" => $message,
                "date" => Yii::$app->formatter->asDatetime($instance->last_updated, 'php:Y-m-d H:i:s') . ' ' . date_default_timezone_get(),
                "count" => "{$text_value} {$element_narrative->getLimitTypeName()}",
                "limit_status" => $limit_status
            ];
        }
    }

    public function actionSaveDropdownInstance()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id  = Yii::$app->user->id;

        $request = Yii::$app->request;

        if ($request->isAjax) {
            $data = $request->post();

            $instance = ElementDropdownInstances::findOne([
                'user_id' => $user_id,
                'template_id' => $data['template_id'],
                'element_id' => $data['element_id'],
            ]);

            if ($data['option_id'] === null || $data['option_id'] === '') {
                // User selected "Select an Option", delete the record
                if ($instance && $instance->delete()) {
                    return ['status' => 'deleted', 'message' => 'Instance deleted successfully.'];
                }
                return ['status' => 'error', 'message' => 'Couldn\'t delete the record'];
            }

            if (!$instance) {
                $instance = new ElementDropdownInstances();
                $instance->user_id = $user_id;
                $instance->template_id = $data['template_id'];
                $instance->element_id = $data['element_id'];
            }

            $instance->option_id = $data['option_id'];

            if ($instance->save()) {
                return ['status' => 'success', 'message' => 'Instance updated successfully.'];
            } else {
                return ['status' => 'error', 'errors' => $instance->getErrors()];
            }
        }


        throw new \yii\web\BadRequestHttpException('Invalid request.');
    }
    
    public function actionSaveTableInstance()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id  = Yii::$app->user->id;

        $request = Yii::$app->request;

        if ($request->isAjax) {
            $data = $request->post();

                
            $postData = Yii::$app->request->post('data');
            

            $instance = ElementTableInstances::findOne([
                'user_id' => $user_id,
                'template_id' => $data['template_id'],
                'element_id' => $data['element_id'],
            ]);


            if (!$instance) {
                $instance = new ElementTableInstances();
                $instance->user_id = $user_id;
                $instance->template_id = $data['template_id'];
                $instance->element_id = $data['element_id'];
            }


            if (empty($data['table_data'])){
                if ($instance->delete()){
                    return ['status' => 'success', 'message' => 'Instance deleted successfully.' , 'last_updated_message' => null];
                } else {
                    // Error occurred while deleting
                    throw new \yii\base\Exception("Error deleting record: " . implode(", ", $instance->getFirstErrors()));
                }

            } else {

                // Save as JSON
                $instance->table_data = json_encode($data['table_data']);

                if ($instance->save()) {

                    $last_updated_message = CommonUtils::timeSinceUpdate($instance->last_updated);
                    return ['status' => 'success', 'message' => 'Instance updated successfully.', 'last_updated_message' => $last_updated_message];

                } else {
                    // Error occurred while saving
                    throw new \yii\base\Exception("Error saving record: " . implode(", ", $instance->getFirstErrors()));
                }
            }
        }


        throw new \yii\web\BadRequestHttpException('Invalid request.');
    }

    public function actionCreateBulletedListItem() {
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = new ElementBulletedListItem();
        $model->attributes = Yii::$app->request->post();
        $model->user_id = Yii::$app->user->id;
        $model->last_updated = date('Y-m-d H:i:s');

        if ($model->save()) {
            return [
                'id' => $model->id,
            ];
        }
        
        throw new \yii\base\Exception("List item not saved");
    }

    public function actionUpdateBulletedListItem() {
        
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->request->post();

        $item_id = $post['item_id'];
        $value = $post['value'] ?? '';

        if (!$item_id) {
            throw new \yii\base\Exception("List item id not provided");
        }

        $item = ElementBulletedListItem::findOne($item_id);
        if (!$item) {
            throw new \yii\web\NotFoundHttpException("List item not found " + $item_id);
        }

        $item->value = $value;
        $item->last_updated = date('Y-m-d H:i:s');

        if ($item->save()) {
            return [
                'message' => 'List item successfully updated.',
                'last_updated' => [
                    'timestamp' => Yii::$app->formatter->asDatetime($item->last_updated, 'php:Y-m-d H:i:s') . ' ' . date_default_timezone_get(),
                    'message' => CommonUtils::timeSinceUpdate($item->last_updated)
                ]
            
            ];
        }
        
        throw new \yii\base\Exception("Failed to update list item " + $item_id);

    }

    public function actionDeleteBulletedListItem() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        // Retrieve the item_id from the POST request
        $item_id = Yii::$app->request->post('item_id');
    
        if (!$item_id) {
            throw new \yii\base\Exception("List item id not provided");
        }
    
        // Find the model based on the item_id
        $model = ElementBulletedListItem::findOne(['id' => $item_id]);
    
        if (!$model) {
            throw new \yii\web\NotFoundHttpException("List item not found " + $item_id);
        }

        // Delete the model if it exists
        if ($model->delete()) {
            return ['message' => 'List item successfully deleted.' ];
        } 

        throw new \yii\base\Exception("Failed to delete list item " + $item_id);

    }
    

    public function actionExportPdf($orcid, $template_url_name)
    {
        $data = $this->actionProfile($orcid, $template_url_name, true);

        $htmlContent = $this->renderPartial('pdf_template', $data);
        $htmlContent = trim($htmlContent); // Remove any leading/trailing whitespace or extra characters
        // return $htmlContent; // uncomment this to see html page for debugging
        $client = Yii::$app->httpClient;
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl(Yii::$app->params['pdfExportService'] . '/generate-pdf')
            ->setData(['html' => $htmlContent])
            ->send();

        if ($response->isOk) {
            Yii::$app->response->format = Response::FORMAT_RAW;
            Yii::$app->response->headers->add('Content-Type', 'application/pdf');
            Yii::$app->response->headers->add('Content-Disposition', 'attachment'); // Forces download
            return $response->content;
        } else {
            return "Failed to generate PDF.";
        }
    }
}