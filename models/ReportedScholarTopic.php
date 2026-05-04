<?php

namespace app\models;

use yii\db\ActiveRecord;

class ReportedScholarTopic extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%reported_scholar_topics}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'paper_id', 'topic_id'], 'required'],
            [['user_id', 'paper_id'], 'integer'],
            [['topic_id'], 'string', 'max' => 100],
            [['created_at'], 'safe'],
            [['user_id', 'paper_id', 'topic_id'], 'unique', 'targetAttribute' => ['user_id', 'paper_id', 'topic_id']],
        ];
    }

    public static function isReported($user_id, $paper_id, $topic_id)
    {
        return self::find()
            ->where([
                'user_id' => (int) $user_id,
                'paper_id' => (int) $paper_id,
                'topic_id' => (string) $topic_id,
            ])
            ->exists();
    }
}
