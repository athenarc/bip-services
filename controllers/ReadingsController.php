<?php

namespace app\controllers;

use app\models\Article;
use app\models\Indicators;
use app\models\Involvement;
use app\models\Notes;
use app\models\ReadingList;
use app\models\Readings;
use app\models\SavedReadingList;
use app\models\SearchForm;
use app\models\User;
use app\models\UsersFolders;
use app\models\UsersLikes;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\StringHelper;
use yii\helpers\Url;

class ReadingsController extends BaseController {
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

    public function actionIndex() {
        $user_id = Yii::$app->user->id;
        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->render('readings_landing', [
            'user_id' => $user_id,
            'impact_indicators' => $impact_indicators,
        ]);
    }

    public function actionList($reading_list_id = null) {
        $owner_user_id = null;
        $viewer_user_id = Yii::$app->user->id;

        $current_reading_list = null;

        if (isset($reading_list_id)) {
            $current_reading_list = ReadingList::find()->where(['id' => $reading_list_id])->one();

            // if specified reading list is not found or it is not public, throw not found
            if (! $current_reading_list || (! $current_reading_list->is_public && $current_reading_list->user_id !== Yii::$app->user->id)) {
                throw new \yii\web\NotFoundHttpException('Reading List was Not Found');
            }

            $owner_user_id = $current_reading_list->user_id;

            // load reading list's stored facet values
            $facets = json_decode($current_reading_list->facets);
            // when a user wants to sort the current reading list
            $sort_field = (isset($_GET['sort'])) ? Yii::$app->request->get('sort') : $facets->sort;
            // For list views (including public users), allow ad-hoc facet interaction
            // by honoring GET filters; fallback to stored list facets when absent.
            $topics = Yii::$app->request->get('topics', $facets->topics ?? null);
            $tags = Yii::$app->request->get('tags', $facets->tags ?? null);
            $rd_status = Yii::$app->request->get('rd_status', $facets->rd_status ?? null);
            $accesses = Yii::$app->request->get('accesses', $facets->accesses ?? null);
            $types = Yii::$app->request->get('types', $facets->types ?? null);
        } else {
            $owner_user_id = $viewer_user_id;

            // redirect to login page, if not already logged in
            if (! isset($viewer_user_id)) {
                Url::remember();

                return $this->redirect(['site/login']);
            }

            $sort_field = (isset($_GET['sort'])) ? Yii::$app->request->get('sort') : 'year';

            $topics = Yii::$app->request->get('topics');
            $tags = Yii::$app->request->get('tags');
            $rd_status = Yii::$app->request->get('rd_status');
            $accesses = Yii::$app->request->get('accesses');
            $types = Yii::$app->request->get('types');
        }

        // replace empty access with null, indicating unknown
        if (! empty($accesses)) {
            $accesses = array_map(function ($r) { return ($r === '') ? null : $r; }, $accesses);
        }

        $user = User::findIdentity($owner_user_id);

        $readings = new Readings($user);

        // fetch papers in current page
        $result = $readings->get($topics, $tags, $rd_status, $accesses, $types, $sort_field);

        // Reading lists can be created only from user-defined tags:
        // at least one tag selected and no other facet selected.
        $reading_list_enable = ! empty($tags) &&
            empty($topics) &&
            empty($accesses) &&
            empty($rd_status) &&
            empty($types);

        // get last selected facet field and its value
        $facet_field = Yii::$app->request->get('fct_field');

        $result['facets'] = $readings->getFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field);

        // fetch involvement
        $result = Involvement::getInvolvement($result, $owner_user_id);

        // attach code repository URLs
        $result['papers'] = Article::getCodeRepoUrls($result['papers']);

        // for guest viewers (public list page), there are no own or saved lists
        // and the sidebar is hidden in the view, so skip the user-scoped queries
        $own_reading_lists = isset($viewer_user_id)
            ? ReadingList::find()
                ->where(['user_id' => $viewer_user_id])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_DESC])
                ->all()
            : [];

        $saved_links = isset($viewer_user_id)
            ? SavedReadingList::find()
                ->where(['user_id' => $viewer_user_id])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all()
            : [];
        $saved_reading_list_ids = array_map(function ($link) {
            return (int) $link->reading_list_id;
        }, $saved_links);

        $saved_reading_lists_ordered = [];

        if (! empty($saved_reading_list_ids)) {
            $lists_by_id = ReadingList::find()
                ->where(['id' => $saved_reading_list_ids])
                ->indexBy('id')
                ->all();

            foreach ($saved_links as $link) {
                $rid = (int) $link->reading_list_id;
                $list = $lists_by_id[$rid] ?? null;

                if ($list !== null && (int) $list->is_public === 1) {
                    $saved_reading_lists_ordered[] = $list;
                } else {
                    $orphan = new \stdClass();
                    $orphan->id = $rid;
                    $orphan->title = 'List deleted by owner';
                    $orphan->user_id = 0;
                    $orphan->description = '';
                    $orphan->is_orphan_saved = true;
                    $saved_reading_lists_ordered[] = $orphan;
                }
            }
        }

        $own_reading_list_ids = array_map(function ($list) {
            return (int) $list->id;
        }, $own_reading_lists);

        $saved_reading_lists_others = [];

        foreach ($saved_reading_lists_ordered as $saved_list) {
            if (! in_array((int) $saved_list->id, $own_reading_list_ids, true)) {
                $saved_reading_lists_others[] = $saved_list;
            }
        }

        $reading_list_owner_labels = [];

        if (! empty($saved_reading_lists_others)) {
            $ownerUserIds = [];

            foreach ($saved_reading_lists_others as $list) {
                if (! empty($list->is_orphan_saved)) {
                    continue;
                }
                $ownerUserIds[(int) $list->user_id] = true;
            }
            $ownerUserIds = array_keys($ownerUserIds);
            $usersById = ! empty($ownerUserIds)
                ? User::find()
                    ->where(['id' => $ownerUserIds])
                    ->indexBy('id')
                    ->all()
                : [];

            foreach ($ownerUserIds as $uid) {
                $ownerUser = $usersById[$uid] ?? null;
                $reading_list_owner_labels[$uid] = ($ownerUser && ! empty($ownerUser->username))
                    ? (string) $ownerUser->username
                    : ('user_' . $uid);
            }
        }

        // edit permissions are granted if no reading list is provided OR the user is the owner of the reading list
        $edit_perm = (isset($current_reading_list) && ($current_reading_list->user_id === $viewer_user_id)) || ! isset($current_reading_list);
        $is_current_list_saved = isset($current_reading_list) && in_array((int) $current_reading_list->id, $saved_reading_list_ids, true);
        $can_save_current_list = isset($viewer_user_id) &&
            isset($current_reading_list) &&
            ! $edit_perm &&
            (int) $current_reading_list->is_public === 1 &&
            ! $is_current_list_saved;

        $current_reading_list_owner_label = null;

        if (isset($current_reading_list) && (int) $current_reading_list->user_id !== (int) $viewer_user_id) {
            $ownerListUserId = (int) $current_reading_list->user_id;

            if (isset($reading_list_owner_labels[$ownerListUserId])) {
                $current_reading_list_owner_label = $reading_list_owner_labels[$ownerListUserId];
            } else {
                $listOwnerUser = User::findOne(['id' => $ownerListUserId]);
                $current_reading_list_owner_label = ($listOwnerUser && ! empty($listOwnerUser->username))
                    ? (string) $listOwnerUser->username
                    : ('user_' . $ownerListUserId);
            }
        }

        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->render('readings', [
            'impact_indicators' => $impact_indicators,
            'result' => $result,
            'edit_perm' => $edit_perm,
            'highlight_key' => 'Readings',
            'orderings' => [
                'year' => 'Publication year',
                'influence' => 'Influence',
                'popularity' => 'Popularity',
                'impulse' => 'Impulse',
                'citation_count' => 'Citation Count'
            ],
            'selected_topics' => $topics,
            'selected_tags' => $tags,
            'selected_rd_status' => $rd_status,
            'selected_accesses' => $accesses,
            'selected_types' => $types,
            'reading_list_enable' => $reading_list_enable,
            'sort_field' => $sort_field,

            'own_reading_lists' => $own_reading_lists,
            'saved_reading_lists_others' => $saved_reading_lists_others,
            'saved_reading_list_ids' => $saved_reading_list_ids,
            'reading_list_owner_labels' => $reading_list_owner_labels,
            'current_reading_list_owner_label' => $current_reading_list_owner_label,
            'current_reading_list' => $current_reading_list,
            'can_save_current_list' => $can_save_current_list,
            'is_current_list_saved' => $is_current_list_saved,
        ]);
    }

    public function actionSaveReadingList() {
        $reading_list_id = Yii::$app->request->post('reading_list_id');
        $user_id = Yii::$app->user->id;

        if (! empty($reading_list_id)) {
            $reading_list = ReadingList::find()->where(['id' => $reading_list_id, 'user_id' => $user_id])->one();

            if (! $reading_list) {
                throw new \yii\web\NotFoundHttpException('Reading list not found.');
            }
        } else {
            $reading_list = new ReadingList();
            $reading_list->user_id = $user_id;
            $reading_list->is_public = 0;
            $maxSortOrder = (int) ReadingList::find()->where(['user_id' => $user_id])->max('sort_order');
            $reading_list->sort_order = $maxSortOrder + 1;
        }

        $reading_list->title = Yii::$app->request->post('new_reading_list_title');
        $reading_list->description = Yii::$app->request->post('new_reading_list_description');
        $reading_list->facets = Yii::$app->request->post('new_reading_list_facets');
        $reading_list->save();

        return $this->redirect(['readings/list/' . $reading_list->id]);
    }

    public function actionSaveSharedReadingList() {
        $user_id = Yii::$app->user->id;

        if (! isset($user_id)) {
            Url::remember();

            return $this->redirect(['site/login']);
        }

        $reading_list_id = (int) Yii::$app->request->post('reading_list_id');
        $reading_list = ReadingList::findOne(['id' => $reading_list_id]);

        if (! $reading_list || (int) $reading_list->is_public !== 1) {
            throw new \yii\web\NotFoundHttpException('Reading list not found.');
        }

        if ((int) $reading_list->user_id === (int) $user_id) {
            return $this->redirect(['readings/list/' . $reading_list_id]);
        }

        $already_saved = SavedReadingList::find()
            ->where(['reading_list_id' => $reading_list_id, 'user_id' => $user_id])
            ->exists();

        if (! $already_saved) {
            $saved_reading_list = new SavedReadingList();
            $saved_reading_list->reading_list_id = $reading_list_id;
            $saved_reading_list->user_id = $user_id;
            $maxSortOrder = (int) SavedReadingList::find()->where(['user_id' => $user_id])->max('sort_order');
            $saved_reading_list->sort_order = $maxSortOrder + 1;
            $saved_reading_list->save();
        }

        return $this->redirect(['readings/list/' . $reading_list_id]);
    }

    public function actionRemoveSavedReadingList() {
        $user_id = Yii::$app->user->id;

        if (! isset($user_id)) {
            Url::remember();

            return $this->redirect(['site/login']);
        }

        $reading_list_id = (int) Yii::$app->request->post('reading_list_id');
        $saved_reading_list = SavedReadingList::findOne([
            'reading_list_id' => $reading_list_id,
            'user_id' => $user_id,
        ]);

        if ($saved_reading_list) {
            $saved_reading_list->delete();
        }

        // if the list is no longer accessible to the viewer (privated or
        // deleted by the owner), redirect back to the default readings view
        // instead of triggering a 404 on the now-removed/private page
        $reading_list = ReadingList::findOne(['id' => $reading_list_id]);
        $can_view_list = $reading_list && (
            (int) $reading_list->user_id === (int) $user_id ||
            (int) $reading_list->is_public === 1
        );

        return $can_view_list
            ? $this->redirect(['readings/list/' . $reading_list_id])
            : $this->redirect(['readings/list']);
    }

    public function actionDeleteReadingList() {
        $selected_list_id = Yii::$app->request->get('selected_list_id');
        $user_id = Yii::$app->user->id;
        // redirect to login page, if not already logged in
        if (! isset($user_id)) {
            Url::remember();

            return $this->redirect(['site/login']);
        }

        $found_list = ReadingList::find()->where(['id' => $selected_list_id])->one();

        if (! empty($found_list) && ($found_list->user_id === $user_id)) {
            $found_list->delete();
        } else {
            throw new \yii\web\NotFoundHttpException('Reading list not found.');
        }

        return $this->redirect(['readings/list']);
    }

    public function actionAjaxUpdatePublicReadingList() {
        $is_public = Yii::$app->request->post('is_public');
        $reading_list_id = Yii::$app->request->post('reading_list_id');

        $reading_list = ReadingList::find()->where(['id' => $reading_list_id])->one();

        if (! $reading_list) {
            throw new \yii\web\NotFoundHttpException('Reading list not found.');
        }

        $reading_list->is_public = $is_public;
        $reading_list->save();
    }

    public function actionAjaxUpdateReadingListsOrder() {
        $user_id = Yii::$app->user->id;

        if (! isset($user_id)) {
            throw new \yii\web\UnauthorizedHttpException('Unauthorized');
        }

        $ordered_ids = Yii::$app->request->post('ordered_ids', []);

        if (! is_array($ordered_ids)) {
            throw new \yii\web\BadRequestHttpException('Invalid payload.');
        }

        $scope = Yii::$app->request->post('order_scope', 'own');

        if ($scope === 'linked') {
            $position = 1;

            foreach ($ordered_ids as $list_id) {
                $list_id = (int) $list_id;

                if ($list_id <= 0) {
                    continue;
                }

                // skip lists owned by this user; they cannot legitimately
                // appear in the "saved (linked)" scope. Privated and orphan
                // entries (owner deleted the underlying list) are intentionally
                // persisted here so the user's drag order survives.
                $reading_list = ReadingList::findOne(['id' => $list_id]);

                if ($reading_list && (int) $reading_list->user_id === (int) $user_id) {
                    continue;
                }

                // always advance position so values stay distinct even when
                // the saved row already had the target sort_order (MariaDB's
                // UPDATE returns 0 changed rows in that case, so we can't rely
                // on updateAll's return value to gate the increment).
                SavedReadingList::updateAll(
                    ['sort_order' => $position],
                    ['user_id' => $user_id, 'reading_list_id' => $list_id]
                );
                $position++;
            }
        } else {
            $position = 1;

            foreach ($ordered_ids as $list_id) {
                $list_id = (int) $list_id;

                if ($list_id <= 0) {
                    continue;
                }

                ReadingList::updateAll(
                    ['sort_order' => $position],
                    ['id' => $list_id, 'user_id' => $user_id]
                );
                $position++;
            }
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return ['success' => true];
    }

    /*
     * Ajax action for changing bookmark reading status
     */
    public function actionAjaxReading() {
        $user_id = Yii::$app->user->id;
        $reading_value = Yii::$app->request->post('reading_value');
        $previous_reading_value = Yii::$app->request->post('previous_reading_value');
        $paper_id = Yii::$app->request->post('paper_id');

        // validate reading value
        if (array_key_exists($reading_value, Yii::$app->params['reading_fields'])) {
            UsersLikes::updateTableValue($user_id, $paper_id, 'reading_status', $reading_value);
        } else {
            throw new \yii\base\Exception();
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'reading_name' => Yii::$app->params['reading_fields'][$reading_value],
            'previous_reading_name' => Yii::$app->params['reading_fields'][$previous_reading_value],
        ];
    }

    /*
     * Ajax action to render bookmark notes
     */
    public function actionLoadNotes() {
        $user_id = Yii::$app->user->id;
        $paper_id = Yii::$app->request->get('paper_id');

        $note = Notes::loadNote($user_id, $paper_id);

        return $this->renderPartial('notes_modal', [
            'editor_content' => $note['notes'],
            'paper' => $note,
            'tags' => $note['tags'],
            'reading_status' => $note['reading_status'],
        ]);
    }

    /*
     * Ajax action for updating bookmark notes
     * Change notes in database
     */

    public function actionSaveNotes() {
        $user_id = Yii::$app->user->id;
        $paper_id = Yii::$app->request->post('paper_id');
        $notes_value = Yii::$app->request->post('notes');

        Notes::updateNotes($user_id, $paper_id, $notes_value);
    }

    public function actionGenerateInsights() {
        set_time_limit(300);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $pdf_url = Yii::$app->request->post('pdf_url');


        $client = new \yii\httpclient\Client();

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('https://insightguide-api.imsi.athenarc.gr/api/process-pdf-url/')
            ->setHeaders(['Content-Type' => 'application/json'])
            ->setOptions([
                CURLOPT_TIMEOUT => 300,
                CURLOPT_CONNECTTIMEOUT => 30,
            ])
            ->setContent(json_encode([
                'pdf_url' =>  $pdf_url
            ]))
            ->send();

        if ($response->isOk) {
            return $response->data;
        }

        return [
        'error' => true,
        ];
    }

    //####################################################################################################################
    //#                                           OLD (DEPRECATED) FAVORITES ACTIONS                                    ##
    //####################################################################################################################

    public function actionFavorites() {
        // Get current user
        $user = Yii::$app->user->id;

        if (! isset($user)) {
            Url::remember();

            return $this->redirect(['site/login']);
        }

        $folder_id = Yii::$app->request->get('id');
        // encode id
        $encoding_prefix = $user . '@123456789';

        //Get bookmark folders
        $folders = UsersFolders::find()->where(['user_id' => $user])->all();

        if (isset($folder_id)) {
            $folder_id = StringHelper::base64UrlDecode($folder_id);
            $folder_id = str_replace($encoding_prefix, '', $folder_id);

            // avoid user accessing other users folders
            $exists = UsersFolders::find()->where(['user_id' => $user, 'id' => $folder_id])->exists();

            if (! $exists and $folder_id != 'null') {
                throw new \yii\web\NotFoundHttpException('Folder not found');
            }

            // misc. bookmarks make request with folder_id : "null"
            $folder_id = ($folder_id === 'null') ? null : $folder_id;

            //Get the bookmarks for the given folder
            $papers = UsersLikes::getUserPapersInFolder($user, $folder_id)->all();
            $folder_contents = SearchForm::get_impact_class($papers);

            if ($folder_id === null) {
                $folder_info['name'] = 'Misc. Bookmarks';
            } else {
                // array  with info about each folder : name, total papers, total read
                $folder_info['name'] = UsersFolders::find()->where(['id' => $folder_id])->one()['name'];
                $folder_info['num_articles'] = UsersLikes::find()->where(['folder_id' => $folder_id])->count();
                $folder_info['total_read'] = UsersLikes::find()->where(['folder_id' => $folder_id, 'reading_status' => 2])->count();
            }
            // convert back to null if needed
            $folder_id = ($folder_id === null) ? 'null' : $folder_id;

            return $this->render('favorites/favorites', [
                'user' => $user,
                'folder_id' => $folder_id,
                'folders' => $folders,
                'folder_contents' => $folder_contents,
                'folder_info' => $folder_info,
                'encoding_prefix' => $encoding_prefix
            ]);
        }

        // total number of bookmarks
        $user_likes_num = UsersLikes::countUserLikes($user);

        return $this->render('favorites/favorites', [
                'user' => $user,
                'folders' => $folders,
                'user_likes_num' => $user_likes_num,
                'encoding_prefix' => $encoding_prefix,
                'highlight_key' => 'Bookmarks'
            ]);
    }

    public function actionCreatefolder() {
        //get the user id
        $user_id = Yii::$app->user->id;

        if (isset($_POST['fname'])) {
            //Create a new instance of the folder model, based on the user's input
            $folder = new UsersFolders();

            //set the id of the connected user
            $folder->user_id = $user_id;

            //set the name of the folder
            $folder->name = $_POST['fname'];

            //Before saving, validate the instance.
            if ($folder->validate()) {
                $folder->save();

                return $this->redirect('favorites');
            }
            $err = $folder->getErrors();

            return $this->render('favorites/create_folder', [
                    'user_id' => $user_id,
                    'err' => $err
                ]);
        }

        return $this->render('favorites/create_folder', [
                'user_id' => $user_id
            ]);
    }

    public function actionEditfolder() {
        $folder_id = Yii::$app->request->post('folder_id'); //get the folder id (from the previously submitted form-button)
        $folder = UsersFolders::find()->where(['id' => $folder_id])->one(); //find the folder of which name will be update

        if (isset($_POST['fname'])) {
            $folder->name = $_POST['fname']; //set the name of the folder
            //Before updating, validate the instance.
            if ($folder->validate()) {
                $folder->update(); //since all are okay, then update in database

                return $this->redirect('favorites');
            }

            $err = $folder->getErrors();

            return $this->render('favorites/edit_folder', ['user_id' => $user_id, 'err' => $err]);
        }
        //if action ignited after form-button click

        return $this->render('favorites/edit_folder', ['folder' => $folder]);
    }

    public function actionRemovefolder() {
        //get the folder id
        $folder_id = Yii::$app->request->post('folder_id');

        // find folder
        $folder = UsersFolders::find()->where(['id' => $folder_id])->one();

        if ($folder !== null) {
            // remove all bookmarks from this folder
            UsersFolders::removeBookmarks($folder_id);

            // delete folder
            $folder->delete();

            return $this->redirect('favorites');
        }
    }

    public function actionMovefolder() {
        //get the user id
        $user_id = Yii::$app->user->id;

        //get the id of the bookmark to be moved
        $bookmark_id = Yii::$app->request->post('bookmark_id');

        //get the bookmark to be moved
        $bookmark = UsersLikes::find()->where(['id' => $bookmark_id])->one();

        if (isset($_POST['fid'])) {
            $folder_id = Yii::$app->request->post('fid');

            // if folder_is === 1, then move folder to not organized section
            $bookmark->folder_id = ($folder_id != -1) ? $folder_id : null;
            $bookmark->update();

            return $this->redirect('favorites');
        }

        //get all folders of current user
        $folders = UsersFolders::find()->where(['user_id' => $user_id])->all();

        //render the move-folder-form
        return $this->render('favorites/move_folder', [
                'bookmark' => $bookmark,
                'folders' => $folders
            ]);
    }

    /*
     * Return folder read percentage & total folder articles
     */
    public function actionAjaxupdatefavorites() {
        $user_id = Yii::$app->user->id;
        $folder_id = Yii::$app->request->post('folder_id');

        // Currently only Non Misc. bookmarks are updated
        // folder_id is unique for every user
        // misc. bookmarks all have same folder_id = NULL
        // if folder id is Null: "where user_id" and "where showit 1" are needed

        $folder_articles = UsersLikes::find()->where(['folder_id' => $folder_id])->count();
        $folder_read = UsersLikes::find()->where(['folder_id' => $folder_id, 'reading_status' => 2])->count();

        $folder_articles_str = $folder_articles . (($folder_articles != 1) ? ' articles' : ' article');
        $percent_read = (empty($folder_articles) ? '' : ' - ' . round(100 * ($folder_read / $folder_articles), 0) . '% read');

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'folder_articles' => $folder_articles,
            'percent_read' => $percent_read,
            'folder_articles_str' => $folder_articles_str
        ];
    }
}
