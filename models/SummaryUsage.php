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
        return self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['DATE(created_at)' => date('Y-m-d')])
            ->count() >= 20;
    }
}