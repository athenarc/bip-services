<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ProfileReport extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_DISMISSED = 'dismissed';

    public static function tableName()
    {
        return '{{%profile_reports}}';
    }

    public function rules()
    {
        return [
            [['reported_orcid', 'reporter_user_id', 'reason'], 'required'],
            [['reporter_user_id'], 'integer'],
            [['reported_orcid'], 'string', 'max' => 19],
            [['reason'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_REVIEWED, self::STATUS_RESOLVED, self::STATUS_DISMISSED]],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reported_orcid' => 'Reported Profile ORCID',
            'reporter_user_id' => 'Reporter User ID',
            'reason' => 'Reason',
            'description' => 'Description',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getReporter()
    {
        return $this->hasOne(User::class, ['id' => 'reporter_user_id']);
    }

    public function getReportedResearcher()
    {
        return $this->hasOne(Researcher::class, ['orcid' => 'reported_orcid']);
    }

    public static function create($reported_orcid, $reporter_user_id, $reason, $description = null)
    {
        $report = new self();
        $report->reported_orcid = $reported_orcid;
        $report->reporter_user_id = $reporter_user_id;
        $report->reason = $reason;
        $report->description = $description;
        $report->status = self::STATUS_PENDING;

        if ($report->save()) {
            return $report;
        }

        return null;
    }
}
