<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\AdminOptions;

class SummaryUsage extends ActiveRecord
{
    public static function tableName()
    {
        return 'summary_usage';
    }

    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
        ];
    }

    public static function logAndCheckQuota($userId)
    {
        if (self::isAdmin($userId)) {
            return true;
        }

        $threshold = (int) (AdminOptions::getValue('summarize_button_threshold') ?? 20);

        $count = self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['DATE(created_at)' => date('Y-m-d')])
            ->count();

        error_log("CHECK_QUOTA: user $userId has $count / $threshold");

        if ($count >= $threshold) {
            error_log("QUOTA REACHED for user $userId");
            return false;
        }

        $log = new self();
        $log->user_id = $userId;
        $log->created_at = date('Y-m-d H:i:s'); 

        if (!$log->save()) {
            error_log("FAILED TO SAVE LOG for user $userId");
            error_log(print_r($log->getErrors(), true));
        } else {
            error_log("LOG SAVED for user $userId");
        }
        return true;
    }

    public static function isQuotaReached($userId)
    {
        if (self::isAdmin($userId)) {
            return false; 
        }

        // Get threshold from admin_options or fallback to 20
        $threshold = (int) (AdminOptions::getValue('summarize_button_threshold') ?? 20);

        // Count user summaries today
        $count = self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['DATE(created_at)' => date('Y-m-d')])
            ->count();

        // Log the values for debugging
        error_log("SUMMARY QUOTA CHECK for user_id=$userId: count=$count / threshold=$threshold");
        
        return [
            'used' => $count,
            'limit' => $threshold,
            'quotaReached' => $count >= $threshold,
        ];
    }


    public static function isAdmin($userId)
    {
        return (bool) Yii::$app->db->createCommand("SELECT is_admin FROM users WHERE id = :id")
            ->bindValue(':id', $userId)
            ->queryScalar();
    }

    public static function isAiAssistantEnabledForCurrentUser(): bool
    {
        $user = Yii::$app->user;
        
        if ($user->isGuest) {
            return false;
        }

        return !empty($user->identity->ai_features);
    }
}