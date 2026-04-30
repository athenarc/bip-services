<?php

namespace app\models;

use yii\db\ActiveRecord;

class ProfileTemplateFeedback extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_DENIED = 'denied';

    public static function tableName()
    {
        return '{{%profile_template_feedback}}';
    }

    public function rules()
    {
        return [
            [['template_id', 'profile_orcid', 'reporter_user_id', 'message'], 'required'],
            [['template_id', 'reporter_user_id', 'resolved_by_user_id'], 'integer'],
            [['message', 'admin_note'], 'string'],
            [['profile_orcid'], 'string', 'max' => 19],
            [['status'], 'string', 'max' => 20],
            [['resolved_at', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_RESOLVED, self::STATUS_DENIED]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => 'Template',
            'profile_orcid' => 'Profile ORCID',
            'reporter_user_id' => 'Submitted By',
            'message' => 'Feedback',
            'status' => 'Status',
            'admin_note' => 'Admin Note',
            'resolved_by_user_id' => 'Resolved By',
            'resolved_at' => 'Resolved At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getTemplate()
    {
        return $this->hasOne(Templates::class, ['id' => 'template_id']);
    }

    public function getReporter()
    {
        return $this->hasOne(User::class, ['id' => 'reporter_user_id']);
    }

    public function getResolver()
    {
        return $this->hasOne(User::class, ['id' => 'resolved_by_user_id']);
    }
}
