<?php

namespace app\controllers;

use app\components\common\CommonUtils;
use app\models\Article;
use app\models\CvNarrative;
use app\models\ElementBulletedList;
use app\models\ElementBulletedListItem;
use app\models\ElementContributions;
use app\models\ElementDividers;
use app\models\ElementDropdown;
use app\models\ElementDropdownInstances;
use app\models\ElementFacets;
use app\models\ElementIndicators;
use app\models\ElementNarrativeInstances;
use app\models\ElementNarratives;
use app\models\ElementTable;
use app\models\ElementTableInstances;
use app\models\Indicators;
use app\models\Involvement;
use app\models\Orcid;
use app\models\ProfileReportForm;
use app\models\ProfileTemplateCategories;
use app\models\Researcher;
use app\models\ResponsibleAcadAge;
use app\models\Scholar;
use app\models\ScholarIndicators;
use app\models\ScholarSearchForm;
use app\models\Templates;
use app\models\User;
use Yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Response;

class ScholarController extends BaseController {
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

    /*
     * Ajax action for updating paper involvement
     */
    public function actionAjaxinvolvement() {
        $user_id = Yii::$app->user->id;
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
    public function actionAddRag() {
        $user_id = Yii::$app->user->id;

        if (! isset($user_id)) {
            Url::remember(['scholar/profile']);

            return $this->redirect(['site/login']);
        }

        $researcher = Researcher::findOne(['user_id' => $user_id]);
        $orcid = $researcher->orcid;
        $from_date = Yii::$app->request->post('from_date');
        $to_date = Yii::$app->request->post('to_date');
        $description = Yii::$app->request->post('description');

        $rag_response = ResponsibleAcadAge::updateRag($orcid, $from_date, $to_date, $description);
        $found_date_period = $rag_response['found'];
        $new_rag_data = $rag_response['saved_row'];

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
    public function actionRemoveRag() {
        $user_id = Yii::$app->user->id;

        if (! isset($user_id)) {
            Url::remember(['scholar/profile']);

            return $this->redirect(['site/login']);
        }

        $rag_id = Yii::$app->request->post('rag_id');

        ResponsibleAcadAge::removeRag($rag_id);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    }

    /*
     * Ajax action for updating Responsible academic age html value(rag)
     * alias: Fair academic age
     */
    public function actionUpdateRag() {
        $user_id = Yii::$app->user->id;

        if (! isset($user_id)) {
            Url::remember(['scholar/profile']);

            return $this->redirect(['site/login']);
        }
        $researcher = Researcher::findOne(['user_id' => $user_id]);
        $orcid = $researcher->orcid;
        $min_year = Yii::$app->request->post('min_year');
        $academic_age = Yii::$app->request->post('academic_age');

        $rag_data = ResponsibleAcadAge::get_responsible_academic_age_data($orcid);
        $responsible_academic_age = ScholarIndicators::get_responsible_academic_age($academic_age, $rag_data, $min_year);

        $responsible_academic_age = (! isset($responsible_academic_age)) ? '-' : $responsible_academic_age;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return ['responsible_academic_age' => $responsible_academic_age];
    }

    public function actionIndex() {
        $user_id = Yii::$app->user->id;
        $researcher = Researcher::findOne(['user_id' => $user_id]);

        return $this->render('scholar_landing', [
            'researcher' => $researcher,
        ]);
    }

    public function actionMyprofile($template_url_name = null) {
        $user_id = Yii::$app->user->id;

        // redirect to login page, if not already logged in
        if (! isset($user_id)) {
            Url::remember();

            return $this->redirect(['site/login']);
        }

        $researcher = Researcher::findOne(['user_id' => $user_id]);

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

        $template_url_name = $template_url_name ?? Yii::$app->params['defaultTemplateUrlName'];

        $template = null;

        if (isset($orcid)) {
            $researcher = Researcher::findOne(['orcid' => $orcid]);

            // if user with specified orcid not found or its profile is not public, throw not found
            if (! $researcher || (! $researcher->is_public && $researcher->user_id !== Yii::$app->user->id)) {
                throw new \yii\web\NotFoundHttpException('BIP! Scholar Profile Not Found');
            }

            // if (isset($cv_narrative_id)) {
            //     $current_cv_narrative = CvNarrative::findOne([ 'id' => $cv_narrative_id ]);

            //     if (!$template) {
            //         throw new \yii\web\NotFoundHttpException("BIP! Scholar Template Not Found");
            //     }
            // }

            // check if template is found and its visibility
            $template = Templates::findOne(['url_name' => $template_url_name]);

            if (! $template) {
                throw new \yii\web\NotFoundHttpException('BIP! Scholar Template Not Found');
            }
        } else {
            $user_id = Yii::$app->user->id;

            // redirect to login page, if not already logged in
            if (! isset($user_id)) {
                Url::remember();

                return $this->redirect(['site/login']);
            }

            // does not have {orcid} in path, redirect
            $researcher = Researcher::findOne(['user_id' => $user_id]);

            if ($researcher) {
                return $this->redirect(['scholar/profile/' . $researcher->orcid]);
            }
        }

        // check if the request comes from cv-narrative modal
        // $is_cv_narrative_pjax = Yii::$app->request->get('_pjax') === "#cv-narrative-works-container";

        $edit_perm = isset($researcher) && ($researcher->user_id === Yii::$app->user->id);

        $auth_code = Yii::$app->request->get('code');

        $listsFilters = Yii::$app->request->get('lists', []);

        // if auth_code is present, user has requested to link account with orcid profile
        if (isset($auth_code) && ! isset($researcher->access_token)) {
            $redirect_url = Url::to(['scholar/profile'], true);
            $response = Orcid::authorize($auth_code, $redirect_url);

            // researcher already exists (e.g. different users try to authorise with  the same ORCID account)
            $researcher_exists = Researcher::findOne(['orcid' => $response->orcid]);

            if (! isset($researcher_exists)) {
                $researcher = Researcher::add($user_id, $response->orcid, $response->access_token, $response->name);

                return $this->redirect(['scholar/profile/' . $researcher->orcid]);
            }
        }

        $papers = [];
        $result = [
            'papers' => [],
            'papers_num' => 0,
        ];
        $indicators = [
            'work_types_num' => [
              'papers' => 0,
              'datasets' => 0,
              'software' => 0,
              'other' => 0
            ],
            'popular_works_count' => 0,
            'influential_works_count' => 0,
            'citations_num' => 0,
            'h_index' => 0,
            'i10_index' => 0,
            'popularity' => 0,
            'influence' => 0,
            'impulse' => 0,
            'openness' => '',
            'paper_min_year' => 0,
            'academic_age' => '',
            'responsible_academic_age' => ''
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
            foreach ($template->elements as $element) {
                $config = [];

                switch ($element->type) {
                    case 'Facets':
                        $config = ElementFacets::getConfigFacet($element->id);
                        break;

                    case 'Indicators':
                        $config = ElementIndicators::getConfigIndicator($element->id);
                        break;

                    case 'Contributions List':
                        $config = ElementContributions::getConfigContributions($element->id);
                        break;

                    case 'Section Divider':
                        $config = ElementDividers::getConfigDivider($element->id);
                        break;

                    case 'Dropdown':
                        $config = ElementDropdown::getConfigDropdown($element->id, $template->id, $researcher->user_id);
                        break;

                    case 'Narrative':
                        $config = ElementNarratives::getConfigNarrative($element->id, $template->id, $researcher->user_id);
                        $clean_text = CommonUtils::cleanText($config->value);

                        $text_value = ElementNarratives::countText($config->limit_type, $clean_text);
                        $limit_status = ElementNarratives::getLimitStatus($text_value, $config->limit_value);
                        $count_msg = ElementNarratives::countMessage($config->getLimitTypeName(), $text_value, $config->limit_value);

                        if (! $edit_perm) {
                            // if the text exceeds the limit set by the template, hide the narrative
                            if ($config->limit_value && $text_value > $config->limit_value) {
                                if ($config->hide_when_empty) {
                                    unset($config);
                                } else {
                                    $config->value = '<div class="alert alert-warning text-center" role="alert">This narrative is not displayed since it exceeds the limit set by the template.</div>';
                                }
                            }
                        }
                        break;
                    case 'Bulleted List':
                        $config = ElementBulletedList::getConfig($element->id, $template->id, $researcher->user_id);
                        break;

                    case 'Table':
                        $config = ElementTable::getConfigTable($element->id, $template->id, $researcher->user_id);
                        break;
                    default:
                        throw new \yii\base\Exception('Unknown element type: ' . $element->type);
                }

                // add config for new element
                if (isset($config)) {
                    $template_elements[] = [
                        'element_id' => $element->id,
                        'type' => $element->type,
                        'name' => $element->name,
                        'config' => $config,
                        'messages' => [
                            'limit' => $limit_status ?? '',
                            'count' => $count_msg ?? '',
                        ]
                    ];
                } else {
                    Yii::debug("No config found for element ID {$element->id}, type {$element->type}", __METHOD__);
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

        // Initialize variables that may or may not be populated depending on template elements
        $contributions_lists = [];
        $contributions_indicators = [];
        $contributions_selected_filters = [];
        $facets_linked_to_lists = [];

        if (isset($researcher->access_token)) {
            $scholar = new Scholar($researcher);
            $missing_papers_initialized = false;

            // avoid calculation of redundant information, when the request is not coming from cv-narratives modal.
            // proper modifications were made in the profile view also.
            // if(!$is_cv_narrative_pjax){

            //     if (isset($current_cv_narrative)) {

            //         $cv_narrative_paper_ids = explode(',', $current_cv_narrative->papers);
            //         $scholar->fetchCvNarrativeDois($cv_narrative_paper_ids);
            //         $topics = $tags = $roles = $accesses = $types = null;

            //     }

            // fetch papers in current page
            $indicators_model = new Indicators();
            $rag_data = ResponsibleAcadAge::get_responsible_academic_age_data($researcher->orcid);
            $facet_target_list_id = null;

            foreach ($template_elements as $element) {
                if ($element['type'] !== 'Contributions List') {
                    continue;
                }

                $element_id = $element['element_id'];
                $config = $element['config'];

                $filters = $config['filters'] ?? [];
                $top_k = $config['top_k'] ?? null;
                // Determine sort field: if top_k is set, use config value; otherwise check GET parameter (list-specific)
                $sort_field_local = ! empty($top_k)
                    ? ($config['sort'] ?? 'year')
                    : Yii::$app->request->get('sort_' . $element_id, $config['sort'] ?? 'year');
                $show_pagination = $config['show_pagination'] ?? null;
                $page_size = $config['page_size'] ?? null;

                $pagination_enabled = isset($show_pagination) ? ((int) $show_pagination !== 0) : true;
                $page_size_final = $page_size ?? 10;

                $listsFilters = Yii::$app->request->get('lists', []);
                $topics_for_list = $listsFilters[$element_id]['topics'] ?? ($filters['topics'] ?? null);
                $tags_for_list = $listsFilters[$element_id]['tags'] ?? ($filters['tags'] ?? null);
                $roles_for_list = $listsFilters[$element_id]['roles'] ?? ($filters['roles'] ?? null);
                $accesses_for_list = $listsFilters[$element_id]['accesses'] ?? ($filters['accesses'] ?? null);
                $types_for_list = $listsFilters[$element_id]['types'] ?? ($filters['types'] ?? null);

                if (! empty($accesses_for_list)) {
                    $accesses_for_list = array_map(function ($v) {
                        return ($v === '') ? null : $v;
                    }, (array) $accesses_for_list);
                }
                $contributions_selected_filters[$element_id] = [
                    'topics' => $topics_for_list,
                    'tags' => $tags_for_list,
                    'roles' => $roles_for_list,
                    'accesses' => $accesses_for_list,
                    'types' => $types_for_list
                ];

                $scholar->fetchWorksLimited($sort_field_local, $top_k);

                // Initialize missing papers after first fetchWorksLimited call
                if (! $missing_papers_initialized) {
                    $missing_papers = $scholar->missing_papers;
                    $missing_papers_initialized = true;
                }

                $result = $scholar->getArticlesInPage(
                    $topics_for_list,
                    $tags_for_list,
                    $roles_for_list,
                    $accesses_for_list,
                    $types_for_list,
                    $sort_field_local,
                    $pagination_enabled,
                    $page_size_final,
                    $top_k,
                    'page_list_' . $element_id,
                    'per-page_list_' . $element_id
                );

                // Add involvement data to papers from getArticlesInPage
                if (! empty($result['papers'])) {
                    $result['papers'] = \app\models\Involvement::getInvolvement(
                        ['papers' => $result['papers']],
                        $researcher->user_id
                    )['papers'];
                }

                $all_papers_result = $scholar->getArticlesInPage(
                    $topics_for_list,
                    $tags_for_list,
                    $roles_for_list,
                    $accesses_for_list,
                    $types_for_list,
                    $sort_field_local,
                    false,
                    10000,
                    null // no pagination, large limit
                );

                $result['all_papers'] = \app\models\Involvement::getInvolvement(
                    ['papers' => $all_papers_result['papers']],
                    $researcher->user_id
                )['papers'];

                // If pagination is OFF, force showing all rows for the current filters (respect Top-K)
                if (! $pagination_enabled) {
                    // If Top-K is set, getArticlesInPage already uses it; but to be explicit:
                    if (! empty($top_k)) {
                        $result['papers'] = array_slice($result['papers'], 0, (int) $top_k);
                    } else {
                        // Ensure we truly have *all* rows (some implementations still limit silently)
                        if (! empty($result['all_papers'])) {
                            $result['papers'] = $result['all_papers'];
                        }
                    }

                    // Add involvement data for Top-K papers
                    if (! empty($result['papers'])) {
                        $result['papers'] = \app\models\Involvement::getInvolvement(
                            ['papers' => $result['papers']],
                            $researcher->user_id
                        )['papers'];
                    }

                    $result['papers_num'] = count($result['papers']);
                    $result['pagination'] = null; // no pager in data layer when pagination is off
                }

                $result['selected_papers'] = [];
                $result['selected_papers_num'] = 0;

                $selected_ids_for_list = $scholar->getSelectedPapersForList($element_id);

                if (is_array($selected_ids_for_list) && ! empty($selected_ids_for_list)) {
                    $selected = array_filter($result['all_papers'], function ($paper) use ($selected_ids_for_list) {
                        return in_array($paper['internal_id'], $selected_ids_for_list);
                    });
                    $selected = array_values($selected); // reindex

                    // Respect Top-K if set
                    if (! empty($top_k)) {
                        $selected = array_slice($selected, 0, (int) $top_k);
                    }

                    // Add involvement data for selected papers
                    if (! empty($selected)) {
                        $selected = \app\models\Involvement::getInvolvement(
                            ['papers' => $selected],
                            $researcher->user_id
                        )['papers'];
                    }

                    $result['selected_papers'] = $selected;
                    $result['selected_papers_num'] = count($selected);

                    if ($pagination_enabled) {
                        $result['papers_num'] = $result['selected_papers_num'];
                        $selPagination = new \yii\data\Pagination([
                            'pageSize' => $page_size_final,
                            'totalCount' => $result['papers_num'],
                            'pageParam' => 'page_list_' . $element_id,
                            'pageSizeParam' => 'per-page_list_' . $element_id,
                            'validatePage' => true,
                        ]);

                        $cleanParams = Yii::$app->request->get();
                        unset($cleanParams['page'], $cleanParams['per-page']);
                        $selPagination->params = $cleanParams;

                        $result['pagination'] = $selPagination;
                        $result['papers'] = array_slice($selected, $selPagination->offset, $selPagination->limit);
                    } else {
                        // NO pagination → show ALL selected (still respects Top-K above)
                        $result['pagination'] = null;
                        $result['papers'] = $selected;
                        $result['papers_num'] = count($selected);
                    }
                }

                if ($pagination_enabled) {
                    $result['papers'] = array_slice($result['papers'], 0, $page_size_final);
                }
                $has_selected = ! empty($result['selected_papers']);
                $has_pagination = isset($result['pagination']) &&
                                $pagination_enabled &&
                                method_exists($result['pagination'], 'getPageCount') &&
                                ($result['pagination']->getPageCount() > 1);

                // Decide the natural dataset (what the list currently shows/represents)
                if ($has_pagination) {
                    $dataset_for_facets = $has_selected
                        ? $result['selected_papers']
                        : ($result['all_papers'] ?? $result['papers']);
                } else {
                    $dataset_for_facets = $has_selected ? $result['selected_papers'] : $result['papers'];
                }

                $facet_field_per_list = $listsFilters[$element_id]['fct_field'] ?? null;
                $groupKey = ! empty($facet_field_per_list) ? $this->mapFacetName($facet_field_per_list) : null;

                $result['facets'] = $scholar->getFacets(
                    $topics_for_list,
                    $tags_for_list,
                    $roles_for_list,
                    $accesses_for_list,
                    $types_for_list,
                    $facet_field_per_list
                );

                $originalFacets = $result['facets'];

                if (in_array($groupKey, ['topics', 'tags', 'roles', 'accesses', 'types'], true)) {
                    $recount_others = $this->recountFacetsFromPapers($originalFacets, $dataset_for_facets);

                    $nullTopics = ($groupKey === 'topics') ? null : $topics_for_list;
                    $nullTags = ($groupKey === 'tags') ? null : $tags_for_list;
                    $nullRoles = ($groupKey === 'roles') ? null : $roles_for_list;
                    $nullAccesses = ($groupKey === 'accesses') ? null : $accesses_for_list;
                    $nullTypes = ($groupKey === 'types') ? null : $types_for_list;

                    $baseResult = $scholar->getArticlesInPage(
                        $nullTopics,
                        $nullTags,
                        $nullRoles,
                        $nullAccesses,
                        $nullTypes,
                        $sort_field_local,
                        false,
                        10000,
                        $top_k
                    );

                    $base_papers = Involvement::getInvolvement(['papers' => $baseResult['papers']], $researcher->user_id)['papers'];

                    $selected_ids_for_list = \app\models\Scholar::getSelectedPapersForList($element_id);

                    if (! empty($selected_ids_for_list)) {
                        $idSet = array_flip($selected_ids_for_list);
                        $base_papers = array_values(array_filter($base_papers, static function ($p) use ($idSet) {
                            return isset($idSet[(int) $p['internal_id']]);
                        }));
                    }

                    $onlyActive = [
                        $groupKey => $originalFacets[$groupKey] ?? ['counts' => [], 'options' => []]
                    ];
                    $recount_active = $this->recountFacetsFromPapers($onlyActive, $base_papers);

                    $merged = $recount_others;
                    $merged[$groupKey] = $recount_active[$groupKey] ?? ($merged[$groupKey] ?? []);

                    if (isset($originalFacets[$groupKey]['options'])) {
                        $merged[$groupKey]['options'] = $originalFacets[$groupKey]['options'];
                    }

                    $preAppliedByGroup = [
                        'topics' => (array) ($filters['topics'] ?? []),
                        'tags' => (array) ($filters['tags'] ?? []),
                        'roles' => (array) ($filters['roles'] ?? []),
                        'accesses' => (array) ($filters['accesses'] ?? $filters['availability'] ?? []),
                        'types' => (array) ($filters['types'] ?? $filters['work-type'] ?? []),
                    ];

                    $active = $groupKey;
                    $allowed = $preAppliedByGroup[$active] ?? [];

                    if (! empty($allowed) && isset($merged[$active]['counts']) && is_array($merged[$active]['counts'])) {
                        $normalizeId = function ($id) use ($active) {
                            $id = (string) $id;

                            if (preg_match('~(?:/)?(C?\d+)$~', $id, $m)) {
                                $id = $m[1];
                            }

                            if ($active === 'topics' && ctype_digit($id)) {
                                $id = 'C' . $id;
                            }

                            return $id;
                        };

                        $allowedNorm = array_flip(array_map($normalizeId, $allowed));

                        $merged[$active]['counts'] = array_intersect_key($merged[$active]['counts'], $allowedNorm);

                        if (isset($merged[$active]['options']) && is_array($merged[$active]['options'])) {
                            $merged[$active]['options'] = array_intersect_key($merged[$active]['options'], $allowedNorm);
                        }
                    }

                    $result['facets'] = $this->filterZeroFacets($merged);
                } else {
                    $recounted = $this->recountFacetsFromPapers($originalFacets, $dataset_for_facets);
                    $result['facets'] = $this->filterZeroFacets($recounted);
                }

                $filteredAll = $result['facets'];

                $selectedByGroup = [
                    'topics' => (array) $topics_for_list,
                    'roles' => (array) $roles_for_list,
                    'accesses' => (array) $accesses_for_list,
                    'types' => (array) $types_for_list,
                    'tags' => (array) $tags_for_list,
                ];

                if ($groupKey) {
                    $result['facets'] = $this->restrictNonActiveGroupsToSelected($filteredAll, $selectedByGroup, $groupKey);
                } else {
                    $result['facets'] = $filteredAll;
                }

                $dataset_for_indicators = $dataset_for_facets;
                $contributions_indicators[$element_id] = $indicators_model->computeForPapers($dataset_for_indicators, $rag_data, count($missing_papers ?: []));
                $contributions_indicators[$element_id]['show_missing_papers'] = isset($config['show_missing_papers']) ? (bool) $config['show_missing_papers'] : true;
                $result['selected_accesses'] = $accesses_for_list ?: [];
                $result['selected_types'] = $types_for_list ?: [];

                $contributions_lists[$element_id] = $result;

                if (! isset($facets_linked_to_lists[$element_id])) {
                    $facets_linked_to_lists[$element_id] = [];
                }
                $facets_linked_to_lists[$element_id]['facets_from_list'] = $result['facets'];

                if (Yii::$app->request->isAjax) {
                    $lf = Yii::$app->request->get('lists', []);
                    $ajax_list_id = null;

                    if (is_array($lf) && count($lf) === 1) {
                        $ajax_list_id = (int) array_key_first($lf);
                    }

                    if ($ajax_list_id) {
                        $list_result = $contributions_lists[$ajax_list_id] ?? [];

                        $element_config = [];

                        foreach ($template_elements as $te) {
                            if ($te['element_id'] == $ajax_list_id) {
                                $element_config = $te['config'];
                                break;
                            }
                        }

                        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

                        return $this->renderPartial('@app/components/views/contributions_list_item', [
                            'impact_indicators' => $impact_indicators,
                            'edit_perm' => $edit_perm,
                            'result' => $list_result,
                            'papers' => $list_result['papers'] ?? [],
                            'works_num' => $list_result['papers_num'] ?? 0,
                            'missing_papers' => $missing_papers,
                            'missing_papers_num' => count($missing_papers),
                            'sort_field' => $sort_field,
                            'orderings' => [
                                'year' => 'Publication year',
                                'influence' => 'Influence',
                                'popularity' => 'Popularity',
                                'impulse' => 'Impulse',
                                'citation_count' => 'Citation Count',
                            ],
                            'formId' => 'scholar-form',
                            'element_config' => $element_config,
                            'facets_selected' => $facets_selected,
                            'current_cv_narrative' => null,
                            'selected_accesses' => $list_result['selected_accesses'] ?? [],
                            'selected_types' => $list_result['selected_types'] ?? [],
                        ]);
                    }
                }
            }

            foreach ($template_elements as $element) {
                if ($element['type'] !== 'Facets') {
                    continue;
                }

                $facet_element_id = $element['element_id'];
                $facet_config = ElementFacets::getConfigFacet($facet_element_id);

                foreach ($facet_config as $facet_type => $facet_data) {
                    $linked_list_id = $facet_data['linked_contribution_element_id'] ?? null;

                    if (! $linked_list_id) {
                        continue;
                    }

                    if (! isset($facets_linked_to_lists[$linked_list_id])) {
                        $facets_linked_to_lists[$linked_list_id] = [];
                    }

                    $facets_linked_to_lists[$linked_list_id][$facet_type] = $facet_data;
                }
            }

            // foreach ($template_elements as $element) {
            //     if ($element['type'] === 'Contributions List') {
            //         $element_id = $element['element_id'];
            //         $config = $element['config'];

            //         $filters = $config['filters'] ?? [];
            //         $sort_field_local = $config['sort'] ?? 'year';
            //         $top_k = $config['top_k'] ?? null;
            //         $show_pagination = $config['show_pagination'] ?? null;
            //         $page_size = $config['page_size'] ?? null;

            //         $pagination_enabled = isset($show_pagination) ? ((int)$show_pagination !== 0) : true;
            //         $page_size_final = $page_size ?? 10;

            //         //$use_url_filters = $config['use_url_filters'] ?? false;
            //         $use_url_filters = ((int)$list_id_from_request === (int)$element_id);

            //         $topics_for_list = $use_url_filters ? Yii::$app->request->get('topics', $filters['topics'] ?? null) : ($filters['topics'] ?? null);
            //         $tags_for_list = $use_url_filters ? Yii::$app->request->get('tags', $filters['tags'] ?? null) : ($filters['tags'] ?? null);
            //         $roles_for_list = $use_url_filters ? Yii::$app->request->get('roles', $filters['roles'] ?? null) : ($filters['roles'] ?? null);
            //         $accesses_for_list = $use_url_filters ? Yii::$app->request->get('accesses', $filters['accesses'] ?? null) : ($filters['accesses'] ?? null);
            //         $types_for_list = $use_url_filters ? Yii::$app->request->get('types', $filters['types'] ?? null) : ($filters['types'] ?? null);

            //         $contributions_selected_filters[$element_id] = [
            //             'topics' => $topics,
            //             'tags' => $tags,
            //             'roles' => $roles,
            //             'accesses' => $accesses,
            //             'types' => $types
            //         ];

            //         $scholar->fetchWorksLimited($sort_field_local, $top_k);
            //         $result = $scholar->getArticlesInPage($topics_for_list, $tags_for_list, $roles_for_list, $accesses_for_list, $types_for_list, $sort_field_local, $pagination_enabled, $page_size_final, $top_k);

            //         $facet_field = Yii::$app->request->get('fct_field');
            //         $result['facets'] = $scholar->getFacets($topics_for_list, $tags_for_list, $roles_for_list, $accesses_for_list, $types_for_list, $facet_field);

            //         $result = Involvement::getInvolvement($result, $researcher->user_id);

            //         $contributions_lists_results[$element_id] = $result;

            //         $all_papers = $scholar->indicators->papers;

            //         $contributions_indicators[$element_id] = $indicators_model->computeForPapers($all_papers, $rag_data);

            //         if (!$pagination_enabled && $top_k !== null) {
            //             $contributions_lists_results[$element_id]['papers'] = array_slice($result['papers'], 0, $top_k);
            //             $contributions_lists_results[$element_id]['papers_num'] = min($top_k, count($result['papers']));
            //         }
            //     }

            //     if ($element['type'] === 'Facets') {
            //         $facet_element_id = $element['element_id'];
            //         $facet_config = ElementFacets::getConfigFacet($facet_element_id);

            //         foreach ($facet_config as $facet_type => $facet_data) {
            //             $linked_list_id = $facet_data['linked_contribution_element_id'] ?? null;

            //             if (!$linked_list_id) continue;

            //             if (!isset($facets_linked_to_lists[$linked_list_id])) {
            //                 $facets_linked_to_lists[$linked_list_id] = [];
            //             }

            //             $facets_linked_to_lists[$linked_list_id][$facet_type] = $facet_data;

            //         }
            //     }

            // calculate scholar indicators
            $rag_data = ResponsibleAcadAge::get_responsible_academic_age_data($researcher->orcid);

            // Only compute indicators if scholar->indicators exists (i.e., template has contribution lists)
            if ($scholar->indicators !== null) {
                $indicators = $scholar->indicators->compute($rag_data);
            }
            // Otherwise, $indicators keeps the default empty array initialized earlier

            // attach code repository URLs
            $result['papers'] = Article::getCodeRepoUrls($result['papers']);

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

        $selected_topics = $selected_roles = $selected_accesses = $selected_types = [];
        $listsFilters = Yii::$app->request->get('lists', []);

        // prepare a mapping for selected filters per list
        $selected_per_list = [];

        foreach ($listsFilters as $listId => $filters) {
            $selected_per_list[$listId] = [
                'topics' => $filters['topics'] ?? [],
                'roles' => $filters['roles'] ?? [],
                'accesses' => $filters['accesses'] ?? [],
                'types' => $filters['types'] ?? [],
            ];
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
            'selected_topics' => $selected_topics,
            'selected_roles' => $selected_roles,
            'selected_accesses' => $selected_accesses,
            'selected_types' => $selected_types,
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
            'contributions_lists' => $contributions_lists,
            'contributions_indicators' => $contributions_indicators,
            'contributions_selected_filters' => $contributions_selected_filters,
            'facets_linked_to_lists' => $facets_linked_to_lists,
            'selected_per_list' => $selected_per_list,
        ];

        if ($for_print) {
            return $data;
        }

        return $this->render('profile', $data);
    }

    // used to serve profile indicators to the API
    public function actionProfileIndicators($orcid = null) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (! isset($orcid)) {
            return [];
        }

        $researcher = Researcher::findOne(['orcid' => $orcid]);

        // if user with specified orcid not found or its profile is not public, throw not found
        if (! $researcher || ! $researcher->is_public) {
            throw new \yii\web\NotFoundHttpException('BIP! Scholar Profile Not Found');
        }

        $scholar = new Scholar($researcher);

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

    public function actionReportProfile() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id = Yii::$app->user->id;

        if (! $user_id) {
            return [
                'status' => 'error',
                'message' => 'You must be logged in to report a profile.'
            ];
        }

        $model = new ProfileReportForm();
        $model->reported_orcid = Yii::$app->request->post('reported_orcid');
        $model->reason = Yii::$app->request->post('reason');
        $model->description = Yii::$app->request->post('description');

        if ($model->save()) {
            // Send email notification to admins
            try {
                $reported_researcher = Researcher::findOne(['orcid' => $model->reported_orcid]);
                $reporter = User::findOne($user_id);

                $reported_profile_name = $reported_researcher ? $reported_researcher->name : 'Unknown';
                $reporter_email = $reporter ? $reporter->email : 'Unknown';
                $reporter_username = $reporter ? $reporter->username : 'Unknown';

                $email_body = "A profile has been reported on BIP! Scholar.\n\n";
                $email_body .= "Reported Profile:\n";
                $email_body .= "  Name: {$reported_profile_name}\n";
                $email_body .= "  ORCID: {$model->reported_orcid}\n";
                $email_body .= '  Profile URL: ' . Url::to(['scholar/profile/' . $model->reported_orcid], true) . "\n\n";

                $email_body .= "Reporter Information:\n";
                $email_body .= "  Username: {$reporter_username}\n";
                $email_body .= "  Email: {$reporter_email}\n";
                $email_body .= "  User ID: {$user_id}\n\n";

                $email_body .= "Reason: {$model->reason}\n\n";

                if (! empty($model->description)) {
                    $email_body .= "Additional Details:\n";
                    $email_body .= "{$model->description}\n\n";
                }

                $report_id = $model->getSavedReportId();

                if ($report_id) {
                    $email_body .= "Report ID: {$report_id}\n";
                }

                Yii::$app->mailer->compose()
                    ->setTo(Yii::$app->params['adminEmail'])
                    ->setFrom([Yii::$app->params['adminEmail'] => 'BIP! Services'])
                    ->setSubject('Profile Report: ' . $reported_profile_name . ' (' . $model->reported_orcid . ')')
                    ->setTextBody($email_body)
                    ->send();
            } catch (\Exception $e) {
                // Log error but don't fail the report submission
                Yii::error('Failed to send profile report email: ' . $e->getMessage());
            }

            return [
                'status' => 'success',
                'message' => 'Thank you for your report. We will review it shortly.'
            ];
        }

        return [
                'status' => 'error',
                'message' => ! empty($model->errors) ? implode(' ', array_map(function ($errors) { return implode(' ', $errors); }, $model->errors)) : 'An error occurred while submitting your report.'
            ];
    }

    public function actionSaveCvNarrative() {
        $user_id = Yii::$app->user->id;
        // redirect to login page, if not already logged in
        if (! isset($user_id)) {
            Url::remember();

            return $this->redirect(['site/login']);
        }

        $cv_narrative_id = Yii::$app->request->post('new_cv_narrative_id');

        if (! isset($cv_narrative_id)) {
            // insert new narrative
            $cv_narrative = new CvNarrative();
            $cv_narrative->user_id = $user_id;
        } else {
            // update existing narrative
            $cv_narrative = CvNarrative::find()->where(['id' => $cv_narrative_id])->one();

            if (! $cv_narrative) {
                throw new \yii\base\Exception();
            }
        }

        $cv_narrative->title = Yii::$app->request->post('new_cv_narrative_title');
        $cv_narrative->description = Yii::$app->request->post('new_cv_narrative_description');
        $cv_narrative->papers = Yii::$app->request->post('new_cv_narrative_selected_papers');
        // updates or saves the given record, no need for ->update()
        $cv_narrative->save();

        $researcher = Researcher::findOne(['user_id' => $user_id]);

        return $this->redirect(['scholar/profile/' . $researcher->orcid . '/' . $cv_narrative->id]);
    }

    public function actionDeleteCvNarrative() {
        $selected_cv_narrative_id = Yii::$app->request->get('selected_cv_narrative_id');
        $user_id = Yii::$app->user->id;

        // redirect to login page, if not already logged in
        if (! isset($user_id)) {
            Url::remember();

            return $this->redirect(['site/login']);
        }

        $found_cv_narrative = CvNarrative::find()->where(['id' => $selected_cv_narrative_id])->one();

        if (! empty($found_cv_narrative) && ($found_cv_narrative->user_id === $user_id)) {
            $found_cv_narrative->delete();
        } else {
            throw new \yii\web\NotFoundHttpException('CV narrative not found.');
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
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $template_id = Yii::$app->request->post('template_id');
        $element_id = Yii::$app->request->post('element_id');
        $element_text = Yii::$app->request->post('value');
        $user_id = Yii::$app->user->id;

        // Validate required parameters
        if (! $template_id || ! $element_id || ! $user_id) {
            throw new \yii\base\InvalidParamException('Missing required parameters.');
        }

        $element_narrative = ElementNarratives::findOne(['element_id' => $element_id]);

        if (! $element_narrative) {
            throw new \yii\web\NotFoundHttpException('Element narrative not found.');
        }

        $limit_type = $element_narrative->limit_type ?? null;
        $limit_value = $element_narrative->limit_value ?? null;

        // Fetch existing instance
        $instance = ElementNarrativeInstances::findOne([
            'template_id' => $template_id,
            'element_id' => $element_id,
            'user_id' => $user_id
        ]);

        // If value is empty, delete instance if exists
        if (empty($element_text)) {
            if ($instance && ! $instance->delete()) {
                throw new \yii\base\Exception('Error deleting record: ' . implode(', ', $instance->getFirstErrors()));
            }

            return [
                'message' => CommonUtils::timeSinceUpdate(null),
                'count' => ElementNarratives::countMessage($element_narrative->getLimitTypeName(), 0, $limit_value),
                'limit_status' => null
            ];
        }

        // Clean input text
        $clean_text = CommonUtils::cleanText($element_text);

        // If instance does not exist, create a new one
        if (! $instance) {
            $instance = new ElementNarrativeInstances([
                'template_id' => $template_id,
                'element_id' => $element_id,
                'user_id' => $user_id
            ]);
        }

        // Update value and save
        $instance->value = $element_text;

        if (! $instance->save()) {
            throw new \yii\base\Exception('Error saving record: ' . implode(', ', $instance->getFirstErrors()));
        }

        $text_value = ElementNarratives::countText($element_narrative->limit_type, $clean_text);
        $limit_status = ElementNarratives::getLimitStatus($text_value, $limit_value);
        $count_msg = ElementNarratives::countMessage($element_narrative->getLimitTypeName(), $text_value, $limit_value);

        return [
            'message' => CommonUtils::timeSinceUpdate($instance->last_updated),
            'date' => Yii::$app->formatter->asDatetime($instance->last_updated, 'php:Y-m-d H:i:s') . ' ' . date_default_timezone_get(),
            'count' => $count_msg,
            'limit_status' => $limit_status,
            'count_msg' => $count_msg,
        ];
    }

    public function actionSaveDropdownInstance() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id = Yii::$app->user->id;

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

            if (! $instance) {
                $instance = new ElementDropdownInstances();
                $instance->user_id = $user_id;
                $instance->template_id = $data['template_id'];
                $instance->element_id = $data['element_id'];
            }

            $instance->option_id = $data['option_id'];

            if ($instance->save()) {
                return ['status' => 'success', 'message' => 'Instance updated successfully.'];
            }

            return ['status' => 'error', 'errors' => $instance->getErrors()];
        }

        throw new \yii\web\BadRequestHttpException('Invalid request.');
    }

    public function actionSaveTableInstance() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_id = Yii::$app->user->id;

        $request = Yii::$app->request;

        if ($request->isAjax) {
            $data = $request->post();

            $postData = Yii::$app->request->post('data');

            $instance = ElementTableInstances::findOne([
                'user_id' => $user_id,
                'template_id' => $data['template_id'],
                'element_id' => $data['element_id'],
            ]);

            if (! $instance) {
                $instance = new ElementTableInstances();
                $instance->user_id = $user_id;
                $instance->template_id = $data['template_id'];
                $instance->element_id = $data['element_id'];
            }

            if (empty($data['table_data'])) {
                if ($instance->delete()) {
                    return ['status' => 'success', 'message' => 'Instance deleted successfully.', 'last_updated_message' => null];
                }
                // Error occurred while deleting
                throw new \yii\base\Exception('Error deleting record: ' . implode(', ', $instance->getFirstErrors()));
            } else {
                // Save as JSON
                $instance->table_data = json_encode($data['table_data']);

                if ($instance->save()) {
                    $last_updated_message = CommonUtils::timeSinceUpdate($instance->last_updated);

                    return ['status' => 'success', 'message' => 'Instance updated successfully.', 'last_updated_message' => $last_updated_message];
                }
                // Error occurred while saving
                throw new \yii\base\Exception('Error saving record: ' . implode(', ', $instance->getFirstErrors()));
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

        throw new \yii\base\Exception('List item not saved');
    }

    public function actionUpdateBulletedListItem() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $post = Yii::$app->request->post();

        $item_id = $post['item_id'];
        $value = $post['value'] ?? '';

        if (! $item_id) {
            throw new \yii\base\Exception('List item id not provided');
        }

        $item = ElementBulletedListItem::findOne($item_id);

        if (! $item) {
            throw new \yii\web\NotFoundHttpException('List item not found ' + $item_id);
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

        throw new \yii\base\Exception('Failed to update list item ' + $item_id);
    }

    public function actionDeleteBulletedListItem() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Retrieve the item_id from the POST request
        $item_id = Yii::$app->request->post('item_id');

        if (! $item_id) {
            throw new \yii\base\Exception('List item id not provided');
        }

        // Find the model based on the item_id
        $model = ElementBulletedListItem::findOne(['id' => $item_id]);

        if (! $model) {
            throw new \yii\web\NotFoundHttpException('List item not found ' + $item_id);
        }

        // Delete the model if it exists
        if ($model->delete()) {
            return ['message' => 'List item successfully deleted.'];
        }

        throw new \yii\base\Exception('Failed to delete list item ' + $item_id);
    }

    public function actionExportPdf($orcid, $template_url_name) {
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
        }

        return 'Failed to generate PDF.';
    }

    public function actionSaveSelectedWorks() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $list_id = (int) Yii::$app->request->post('list_id');
        $paper_ids = array_map('intval', (array) Yii::$app->request->post('paper_ids', []));

        if (! $list_id) {
            return ['status' => 'error', 'message' => 'Invalid list_id'];
        }

        try {
            $affected = \app\models\Scholar::saveSelectedPapersForList($list_id, $paper_ids);

            if ($affected > 0) {
                return ['status' => 'success', 'message' => 'Works saved successfully'];
            }

            return ['status' => 'error', 'message' => 'Nothing was saved'];
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);

            return ['status' => 'error', 'message' => 'DB error'];
        }
    }

    private function recountFacetsFromPapers($facets, $papers) {
        $newCounts = [
            'topics' => [],
            'roles' => [],
            'accesses' => [],
            'types' => [],
            'tags' => [],
        ];

        foreach ($papers as $paper) {
            // Topics from concepts
            if (! empty($paper['concepts'])) {
                foreach ($paper['concepts'] as $concept) {
                    // Accept several possible shapes
                    $raw = $concept['id']
                        ?? $concept['concept_id']
                        ?? ($concept['concept']['id'] ?? null)
                        ?? ($concept['openalex_id'] ?? null);

                    if ($raw === null) {
                        continue;
                    }

                    $raw = (string) $raw;

                    if (preg_match('~(?:/)?(C?\d+)$~', $raw, $m)) {
                        $raw = $m[1];
                    }

                    if (ctype_digit($raw)) {
                        $raw = 'C' . $raw;
                    }

                    if (! isset($newCounts['topics'][$raw])) {
                        $newCounts['topics'][$raw] = 0;
                    }
                    $newCounts['topics'][$raw]++;
                }
            }

            // Roles from involvement
            if (! empty($paper['involvement'])) {
                foreach ($paper['involvement'] as $roleId) {
                    if (! isset($newCounts['roles'][$roleId])) {
                        $newCounts['roles'][$roleId] = 0;
                    }
                    $newCounts['roles'][$roleId]++;
                }
            }

            // Accesses
            if (array_key_exists('is_oa', $paper)) {
                $accessId = $paper['is_oa'];

                if (! isset($newCounts['accesses'][$accessId])) {
                    $newCounts['accesses'][$accessId] = 0;
                }
                $newCounts['accesses'][$accessId]++;
            }

            // Types
            if (isset($paper['type'])) {
                $typeId = $paper['type'];

                if (! isset($newCounts['types'][$typeId])) {
                    $newCounts['types'][$typeId] = 0;
                }
                $newCounts['types'][$typeId]++;
            }
        }

        // Reset counts to "0"
        foreach ($facets as $facetType => &$facetData) {
            if (isset($facetData['counts']) && is_array($facetData['counts'])) {
                foreach ($facetData['counts'] as $id => &$oldCount) {
                    $oldCount = '0';
                }
            }
        }

        foreach ($newCounts as $facetType => $counts) {
            foreach ($counts as $id => $count) {
                if (isset($facets[$facetType]['counts'][$id])) {
                    $facets[$facetType]['counts'][$id] = (string) $count;
                } elseif ($facetType === 'topics') {
                    $alt = ltrim($id, 'C');

                    if (isset($facets['topics']['counts'][$alt])) {
                        $facets['topics']['counts'][$alt] = (string) $count;
                    }
                }
            }
        }

        return $facets;
    }

    private function filterZeroFacets($facets, $exceptKey = null) {
        foreach ($facets as $facetType => &$facetData) {
            if ($exceptKey && strtolower($facetType) === strtolower($exceptKey)) {
                continue;
            }

            if (! isset($facetData['counts']) || ! is_array($facetData['counts'])) {
                continue;
            }

            foreach ($facetData['counts'] as $id => $count) {
                if ($count === '0' || $count === 0) {
                    unset($facetData['counts'][$id]);

                    if (isset($facetData['options'][$id])) {
                        unset($facetData['options'][$id]);
                    }
                }
            }
        }

        return $facets;
    }

    private function mapFacetName($name) {
        $n = strtolower((string) $name);

        if ($n === 'topic' || $n === 'topics') {
            return 'topics';
        }

        if ($n === 'tag' || $n === 'tags') {
            return 'tags';
        }

        if ($n === 'role' || $n === 'roles' || $n === 'credit' || $n === 'credit_roles') {
            return 'roles';
        }

        if (in_array($n, ['access', 'accesses', 'availability', 'open_access', 'oa'], true)) {
            return 'accesses';
        }

        if (in_array($n, ['type', 'types', 'work_type', 'work', 'publication', 'publications'], true)) {
            return 'types';
        }

        return $n;
    }

    /**
     * For every facet group EXCEPT the active one, if the user has selections,
     * keep only those selected options (hide the rest), preserving updated counts.
     */
    private function restrictNonActiveGroupsToSelected(array $facets, array $selectedByGroup, ?string $activeGroup): array {
        $active = $activeGroup ? $this->mapFacetName($activeGroup) : null;

        foreach ($selectedByGroup as $group => $ids) {
            $g = $this->mapFacetName($group);

            if ($active && $g === $active) {
                continue;
            }

            if (empty($ids) || ! isset($facets[$g]) || ! is_array($facets[$g])) {
                continue;
            }

            $normIds = array_map(function ($id) use ($g) {
                $id = (string) $id;

                if (preg_match('~(?:/)?(C?\d+)$~', $id, $m)) {
                    $id = $m[1];
                }

                if ($g === 'topics' && ctype_digit($id)) {
                    $id = 'C' . $id;
                }

                return $id;
            }, (array) $ids);

            $oldCounts = $facets[$g]['counts'] ?? [];
            $oldOptions = $facets[$g]['options'] ?? [];

            $newCounts = [];
            $newOptions = [];

            foreach ($normIds as $id) {
                if (isset($oldCounts[$id])) {
                    $newCounts[$id] = $oldCounts[$id];

                    if (isset($oldOptions[$id])) {
                        $newOptions[$id] = $oldOptions[$id];
                    }
                    continue;
                }

                if ($g === 'topics') {
                    $alt = ltrim($id, 'C');

                    if (isset($oldCounts[$alt])) {
                        $newCounts[$alt] = $oldCounts[$alt];
                    }

                    if (isset($oldOptions[$alt])) {
                        $newOptions[$alt] = $oldOptions[$alt];
                    }
                }
            }

            $facets[$g]['counts'] = $newCounts;

            if (! empty($oldOptions)) {
                $facets[$g]['options'] = $newOptions;
            }
        }

        return $facets;
    }
}
