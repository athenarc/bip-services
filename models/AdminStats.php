<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use app\models\UsersLikes;


class AdminStats extends Model {

    public $total_users;
    public $total_scholar_profiles;
    public $total_public_scholar_profiles;
    public $total_users_likes;
    public $total_users_with_likes;

    public function getStats(){

        $this->total_users = self::getTotalUsers();
        $this->total_scholar_profiles = self::getTotalScholarProfiles();
        $this->total_public_scholar_profiles = self::getTotalPublicScholarProfiles();
        $this->total_users_likes = self::getTotalUserLikes();
        $this->total_users_with_likes = self::getTotalUserswithLikes();
    }


    public static function getTotalUsers(){
        return User::find()->count();
    }


    public static function getTotalScholarProfiles(){
        return Researcher::find()->where(['not', ['orcid' => null]])->count();
    }

    public static function getTotalPublicScholarProfiles(){
        return Researcher::find()->where(['not', ['orcid' => null]])->andWhere(['is_public' => 1])->count();
    }


    public static function getTotalUserLikes(){
        return UsersLikes::find()->where(['showit' => 1])->count();
    }

    public static function getTotalUserswithLikes(){
        return UsersLikes::find()->where(['showit' => 1])->select('COUNT(DISTINCT user_id)')->scalar();

    }

    public static function hasAdminAccess(){

        // if user is not logged in or is not admin, throw not found
        if (Yii::$app->user->isGuest  || !Yii::$app->user->identity->is_admin)  {
            return False;
        }
        return True;
        
    }
}