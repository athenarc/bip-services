<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\controllers\BaseController;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;

use app\models\SearchForm;
use app\models\UsersLikes;
use app\models\User;
use app\models\UsersFolders;
use app\models\Orcid;
use app\models\Notes;
use app\models\Involvement;
use app\models\Indicators;
use app\models\Scholar;
use app\models\Readings;
use app\models\ResponsibleAcadAge;
use app\models\ReadingList;
use app\models\ScholarIndicators;

class ReadingsController extends BaseController
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


    public function actionIndex() {

        $user_id = Yii::$app->user->id;

        // user is logged in and has bookmarked papers
        if (isset($user_id) && UsersLikes::UserHasLikes($user_id)) {
            return $this->redirect(['readings/list']);
        }
        $impact_indicators = Indicators::getImpactIndicatorsAsArray('Work');

        return $this->render('readings_landing', [
            'user_id' => $user_id,
            'impact_indicators' => $impact_indicators,
        ]);

    }

    public function actionList($reading_list_id = null) {

        $user_id = null;

        $current_reading_list = null;

        if (isset($reading_list_id)) {
            $current_reading_list = ReadingList::find()->where([ 'id' => $reading_list_id ])->one();

            // if specified reading list is not found or it is not public, throw not found
            if (!$current_reading_list || (!$current_reading_list->is_public && $current_reading_list->user_id !== Yii::$app->user->id))  {
                throw new \yii\web\NotFoundHttpException("Reading List was Not Found");
            }

            $user_id = $current_reading_list->user_id;

            // load reading list's stored facet values
            $facets = json_decode($current_reading_list->facets);
            // when a user wants to sort the current reading list
            $sort_field = (isset($_GET['sort'])) ? Yii::$app->request->get('sort') : $facets->sort;
            $topics = (isset($facets->topics)) ? $facets->topics : null;
            $tags = $facets->tags;
            $rd_status = $facets->rd_status;
            $accesses = $facets->accesses;
            $types = $facets->types;

        } else {
            $user_id = Yii::$app->user->id;

            // redirect to login page, if not already logged in
            if (!isset($user_id)) {
                Url::remember();
                return $this->redirect(['site/login']);
            }

            $sort_field = (isset($_GET['sort'])) ? Yii::$app->request->get('sort') : "year";

            $topics = Yii::$app->request->get('topics');
            $tags = Yii::$app->request->get('tags');
            $rd_status = Yii::$app->request->get('rd_status');
            $accesses = Yii::$app->request->get('accesses');
            $types = Yii::$app->request->get('types');
        }

        // redirect to login page, if not already logged in
        if (!isset($user_id)){
            Url::remember();
            return $this->redirect(['site/login']);
        }

        // replace empty access with null, indicating unknown
        if (!empty($accesses)) {
            $accesses = array_map(function($r) { return ($r === '') ? null : $r; }, $accesses);
        }


        $user = User::findIdentity($user_id);

        $readings = new Readings($user);

        // fetch papers in current page
        $result = $readings->get($topics, $tags, $rd_status, $accesses, $types, $sort_field);

        // topics are not currently used for reading lists
        $reading_list_enable = empty($topics) && ( !empty($tags) || !empty($accesses) || !empty($rd_status) || !empty($types) );

        // get last selected facet field and its value
        $facet_field = Yii::$app->request->get('fct_field');

        $result["facets"] = $readings->getFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field);

        // fetch involvement
        $result = Involvement::getInvolvement($result, $user_id);

        // find all reading lists of the user
        $reading_lists = ReadingList::find()->where([ 'user_id' => $user_id ])->all();

        // edit permissions are granted if no reading list is provided OR the user is the owner of the reading list
        $edit_perm = (isset($current_reading_list) && ($current_reading_list->user_id === Yii::$app->user->id)) || !isset($current_reading_list);
        
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
            'reading_list_enable'=> $reading_list_enable,
            'sort_field' => $sort_field,

            'reading_lists' => ArrayHelper::map($reading_lists, 'id', 'title'),
            'current_reading_list' => $current_reading_list,
        ]);
    }

    public function actionSaveReadingList() {

        $reading_list = new ReadingList();
        $reading_list->title = Yii::$app->request->post('new_reading_list_title');
        $reading_list->description = Yii::$app->request->post('new_reading_list_description');
        $reading_list->user_id = Yii::$app->user->id;
        $reading_list->facets = Yii::$app->request->post('new_reading_list_facets');
        $reading_list->is_public = 0;
        $reading_list->save();

        return $this->redirect(['readings/list/' . $reading_list->id]);
    }

    public function actionDeleteReadingList() {

        $selected_list_id = Yii::$app->request->get('selected_list_id');
        $user_id = Yii::$app->user->id;
        // redirect to login page, if not already logged in
        if (!isset($user_id)) {
            Url::remember();
            return $this->redirect(['site/login']);
        }

        $found_list = ReadingList::find()->where(['id' => $selected_list_id])->one();
        if (!empty($found_list) && ($found_list->user_id === $user_id) ) {
            $found_list->delete();
        } else {
            throw new \yii\web\NotFoundHttpException("Reading list not found.");
        }

        return $this->redirect(['readings/list']);
    }

    public function actionAjaxUpdatePublicReadingList() {

        $is_public = Yii::$app->request->post('is_public');
        $reading_list_id =  Yii::$app->request->post('reading_list_id');

        $reading_list = ReadingList::find()->where([ 'id' => $reading_list_id ])->one();
        if (!$reading_list) {
            throw new \yii\web\NotFoundHttpException("Reading list not found.");
        }

        $reading_list->is_public = $is_public;
        $reading_list->save();
    }

    /*
     * Ajax action for changing bookmark reading status
     */
    public function actionAjaxReading()
    {
        $user_id  = Yii::$app->user->id;
        $reading_value = Yii::$app->request->post('reading_value');
        $previous_reading_value = Yii::$app->request->post('previous_reading_value');
        $paper_id = Yii::$app->request->post('paper_id');


        // validate reading value
        if ( array_key_exists($reading_value, Yii::$app->params['reading_fields']) ) {
            UsersLikes::updateTableValue($user_id, $paper_id, "reading_status", $reading_value);
        }
        else {
            throw new \yii\base\Exception;
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
    public function actionLoadNotes(){

        $user_id  = Yii::$app->user->id;
        $paper_id = Yii::$app->request->get('paper_id');

        $note = Notes::loadNote($user_id, $paper_id);

        return $this->renderPartial('notes_modal', [
            'editor_content' => $note["notes"],
            'paper' => $note,
            'tags' => $note["tags"],
            'reading_status' => $note["reading_status"],
        ]);
    }

    /*
     * Ajax action for updating bookmark notes
     * Change notes in database
     */

    public function actionSaveNotes()
    {
        $user_id  = Yii::$app->user->id;
        $paper_id = Yii::$app->request->post('paper_id');
        $notes_value = Yii::$app->request->post('notes');

        Notes::updateNotes($user_id, $paper_id, $notes_value);

        return;
    }



    #####################################################################################################################
    ##                                           OLD (DEPRECATED) FAVORITES ACTIONS                                    ##
    #####################################################################################################################


    public function actionFavorites()
    {

        // Get current user
        $user = Yii::$app->user->id;

        if (!isset($user)){
            Url::remember();
            return $this->redirect(['site/login']);
        }

        $folder_id = Yii::$app->request->get('id');
        // encode id
        $encoding_prefix = $user.'@123456789';

        //Get bookmark folders
        $folders = UsersFolders::find()->where(['user_id' => $user])->all();

        if (isset($folder_id) ){

            $folder_id  = StringHelper::base64UrlDecode($folder_id);
            $folder_id  = str_replace($encoding_prefix, '', $folder_id);

            // avoid user accessing other users folders
            $exists = UsersFolders::find()->where(['user_id' => $user, 'id'=> $folder_id])->exists();
            if(!$exists and $folder_id!='null'){
                throw new \yii\web\NotFoundHttpException("Folder not found");
            }

            // misc. bookmarks make request with folder_id : "null"
            $folder_id = ($folder_id === 'null') ? NULL : $folder_id;

            //Get the bookmarks for the given folder
            $papers = UsersLikes::getUserPapersInFolder($user, $folder_id)->all();
            $folder_contents = SearchForm::get_impact_class($papers);

            if ($folder_id === NULL){
                $folder_info["name"] = "Misc. Bookmarks";
            } else {
                // array  with info about each folder : name, total papers, total read
                $folder_info["name"] = UsersFolders::find()->where(['id' => $folder_id])->one()['name'];
                $folder_info["num_articles"] = UsersLikes::find()->where(['folder_id' => $folder_id])->count();
                $folder_info["total_read"] = UsersLikes::find()->where(['folder_id' => $folder_id, 'reading_status' => 2])->count();
            }
            // convert back to null if needed
            $folder_id = ($folder_id === NULL) ? 'null' : $folder_id;


            return $this->render('favorites/favorites', [
                'user' => $user,
                'folder_id' => $folder_id,
                'folders' => $folders,
                'folder_contents' => $folder_contents,
                'folder_info' => $folder_info,
                'encoding_prefix' => $encoding_prefix
            ]);

        } else{

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

    }

    public function actionCreatefolder() {
        //get the user id
        $user_id  = Yii::$app->user->id;

        if(isset($_POST['fname']) )
        {
            //Create a new instance of the folder model, based on the user's input
            $folder = new UsersFolders();

            //set the id of the connected user
            $folder->user_id  = $user_id;

            //set the name of the folder
            $folder->name = $_POST['fname'];

            //Before saving, validate the instance.
            if($folder->validate()) {
                $folder->save();
                return $this->redirect('favorites');
            } else {
                $err = $folder->getErrors();
                return $this->render('favorites/create_folder', [
                    'user_id' => $user_id,
                    'err' => $err
                ]);
            }
        } else {
            return $this->render('favorites/create_folder',[
                'user_id' => $user_id
            ]);
        }
    }

    public function actionEditfolder() {

        $folder_id = Yii::$app->request->post('folder_id'); //get the folder id (from the previously submitted form-button)
        $folder = UsersFolders::find()->where(['id'=>$folder_id])->one();; //find the folder of which name will be update

        if(isset($_POST['fname'])) {

            $folder->name = $_POST['fname']; //set the name of the folder
                //Before updating, validate the instance.
            if($folder->validate())
            {
                $folder->update(); //since all are okay, then update in database
                return $this->redirect('favorites');
            }
            else
            {
                $err = $folder->getErrors();
                return $this->render('favorites/edit_folder',['user_id'=>$user_id,'err'=>$err]);
            }

        }
        else //if action ignited after form-button click
        {
            return $this->render('favorites/edit_folder',['folder'=>$folder]);
        }
    }

    public function actionRemovefolder()
    {
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

    public function actionMovefolder()
    {
        //get the user id
        $user_id  = Yii::$app->user->id;

        //get the id of the bookmark to be moved
        $bookmark_id = Yii::$app->request->post('bookmark_id');

        //get the bookmark to be moved
        $bookmark = UsersLikes::find()->where(['id'=>$bookmark_id])->one();

        if(isset($_POST['fid'])) {
                $folder_id = Yii::$app->request->post('fid');

                // if folder_is === 1, then move folder to not organized section
                $bookmark->folder_id = ($folder_id != -1) ? $folder_id : NULL;
                $bookmark->update();
                return $this->redirect('favorites');
        }  else  {

            //get all folders of current user
            $folders = UsersFolders::find()->where(['user_id'=>$user_id])->all();

            //render the move-folder-form
            return $this->render('favorites/move_folder', [
                'bookmark' => $bookmark,
                'folders' => $folders
            ]);
        }
    }

    /*
     * Return folder read percentage & total folder articles
     */
    public function actionAjaxupdatefavorites()
    {
        $user_id  = Yii::$app->user->id;
        $folder_id = Yii::$app->request->post('folder_id');

        // Currently only Non Misc. bookmarks are updated
        // folder_id is unique for every user
        // misc. bookmarks all have same folder_id = NULL
        // if folder id is Null: "where user_id" and "where showit 1" are needed

        $folder_articles = UsersLikes::find()->where(['folder_id' => $folder_id])->count();
        $folder_read = UsersLikes::find()->where(['folder_id' => $folder_id, 'reading_status' => 2 ])->count();

        $folder_articles_str = $folder_articles.(($folder_articles != 1) ? " articles" : " article");
        $percent_read = (empty($folder_articles) ? "" : ' - '.round(100*($folder_read/$folder_articles),0). '% read');

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return [
            'folder_articles' => $folder_articles,
            'percent_read' => $percent_read,
            'folder_articles_str' => $folder_articles_str
        ];
    }

}