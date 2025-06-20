<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

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

        $count = self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['DATE(created_at)' => date('Y-m-d')])
            ->count();

        if ($count >= 20) {
            return false;
        }

        $log = new self();
        $log->user_id = $userId;
        $log->save(false);

        return true;
    }

    public static function isQuotaReached($userId)
    {
        if (self::isAdmin($userId)) {
        return false; 
        }

        return self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['DATE(created_at)' => date('Y-m-d')])
            ->count() >= 20;
    }

    public static function isAdmin($userId)
    {
        return (bool) Yii::$app->db->createCommand("SELECT is_admin FROM users WHERE id = :id")
            ->bindValue(':id', $userId)
            ->queryScalar();
    }
}