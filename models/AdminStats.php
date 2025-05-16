<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use app\models\UsersLikes;
use app\models\User;
use DateTime;
use DateInterval;
use DatePeriod;

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


    public static function getMonthlyUserData(){

        // Determine current range: past 12 months including this month
        $end = new DateTime('first day of this month');
        $end->modify('+1 month');
        $start = (clone $end)->modify('-12 months');

        $startDate = $start->format('Y-m-d');
        $endDate = $end->format('Y-m-d');

        // 1) Get count of users registered before startDate
        $initialCount = (int) User::find()
            ->where(['<', 'registered_at', $startDate])
            ->count();

        // 2) Get monthly counts between start and end
        $users = User::find()
            ->select([
                "DATE_FORMAT(registered_at, '%Y-%m') AS ym",
                "COUNT(*) AS count"
            ])
            ->where(['between', 'registered_at', $startDate, $endDate])
            ->groupBy('ym')
            ->orderBy('ym')
            ->asArray()
            ->all();

        // Prepare month labels and zero-filled counts
        $interval = new DateInterval('P1M');
        $period = new DatePeriod($start, $interval, $end);
        $months = [];
        $monthlyCounts = [];

        foreach ($period as $dt) {
            $key = $dt->format('Y-m');
            $months[$key] = $dt->format('M Y'); // Abbreviated month
            $monthlyCounts[$key] = 0;
        }

        foreach ($users as $row) {
            $monthlyCounts[$row['ym']] = (int) $row['count'];
        }

        // Build cumulative data: start with initialCount, then add each month
        $cumulative = [];
        $runningTotal = $initialCount;

        foreach ($monthlyCounts as $count) {
            $runningTotal += $count;
            $cumulative[] = $runningTotal;
        }

        return [array_values($months), $cumulative];
    
    }


}