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

    public $total_users_likes;
    public $total_users_with_likes;
    public $monthly_user_data;
    public $user_activity_data;
    public $monthly_researcher_data;
    public $researcher_profile_visibility;


    public function getStats(){

        $this->total_users_likes = self::getTotalUserLikes();
        $this->total_users_with_likes = self::getTotalUserswithLikes();
        $this->monthly_user_data = self::getMonthlyData(User::class, 'registered_at');
        $this->user_activity_data = self::getUserActivityData();
        $this->monthly_researcher_data = self::getMonthlyData(Researcher::class, 'created_at');
        $this->researcher_profile_visibility = self::getProfileVisibilityData();
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


    public static function getUserActivityData()
    {
        $now = new \DateTime();
        $activeDate = (clone $now)->modify('-7 days')->format('Y-m-d H:i:s');
        $dormantDate = (clone $now)->modify('-30 days')->format('Y-m-d H:i:s');

        $active = User::find()
            ->where(['>=', 'last_visited', $activeDate])
            ->count();

        $dormant = User::find()
            ->where(['<', 'last_visited', $activeDate])
            ->andWhere(['>=', 'last_visited', $dormantDate])
            ->count();


        // Inactive: > 30 days ago OR never visited (NULL)
        $inactive = User::find()
            ->where([
            'or',
            ['<', 'last_visited', $dormantDate],
            ['last_visited' => null]
            ])
            ->count();

        return [
            'labels' => ['Active (≤7d)', 'Dormant (8-30d)', 'Inactive (>30d)'],
            'data' => [$active, $dormant, $inactive],
        ];
    }

    public static function getProfileVisibilityData()
    {
        $results = Researcher::find()
            ->select(['is_public', 'COUNT(*) AS count'])
            ->groupBy('is_public')
            ->asArray()
            ->all();

        $public = 0;
        $private = 0;

        foreach ($results as $row) {
            if ($row['is_public'] == 1) {
                $public = (int)$row['count'];
            } elseif ($row['is_public'] == 0) {
                $private = (int)$row['count'];
            }
        }

        return [
            'labels' => ['Public', 'Private'],
            'data' => [$public, $private],
        ];
    }


    public static function getMonthlyData($modelClass, $dateField)
    {
        // Validate class exists and is an ActiveRecord
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, \yii\db\ActiveRecord::class)) {
            throw new \InvalidArgumentException("Invalid model class: $modelClass");
        }

        // Determine date range (past 12 months including current month)
        $end = new DateTime('first day of this month');
        $end->modify('+1 month');
        $start = (clone $end)->modify('-12 months');

        $startDate = $start->format('Y-m-d');
        $endDate = $end->format('Y-m-d');

        // 1) Count entries before start date
        $initialCount = (int) $modelClass::find()
            ->where(['<', $dateField, $startDate])
            ->count();

        // 2) Get monthly counts within the date range
        $records = $modelClass::find()
            ->select([
                "DATE_FORMAT($dateField, '%Y-%m') AS ym",
                "COUNT(*) AS count"
            ])
            ->where(['between', $dateField, $startDate, $endDate])
            ->groupBy('ym')
            ->orderBy('ym')
            ->asArray()
            ->all();

        // Create the list of months
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end);
        $months = [];
        $monthlyCounts = [];

        foreach ($period as $dt) {
            $key = $dt->format('Y-m');
            $months[$key] = $dt->format('M Y');
            $monthlyCounts[$key] = 0;
        }

        foreach ($records as $row) {
            $monthlyCounts[$row['ym']] = (int) $row['count'];
        }

        // Build cumulative array
        $cumulative = [];
        $runningTotal = $initialCount;

        foreach ($monthlyCounts as $count) {
            $runningTotal += $count;
            $cumulative[] = $runningTotal;
        }

        return [
            'labels' => array_values($months),
            'data' => $cumulative,
        ];
    }




}
