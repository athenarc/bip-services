<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "like_dislike_annotations".
 *
 * @property int $id
 * @property int $user_id
 * @property string $space_url_suffix
 * @property int $paper_id
 * @property string $annotation_name
 * @property string $annotation_id
 * @property string $action
 */
class LikeDislikeAnnotations extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'like_dislike_annotations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'space_url_suffix', 'paper_id', 'annotation_name', 'annotation_id', 'action'], 'required'],
            [['user_id', 'paper_id'], 'integer'],
            [['space_url_suffix', 'annotation_name', 'annotation_id'], 'string', 'max' => 255],
            [['action'], 'in', 'range' => ['like', 'dislike']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'space_url_suffix' => 'Space URL Suffix',
            'paper_id' => 'Paper ID',
            'annotation_name' => 'Annotation Name',
            'annotation_id' => 'Annotation ID',
            'action' => 'Action',
        ];
    }

    /**
     * Get user's vote for an annotation
     * 
     * @param int $user_id
     * @param int $paper_id
     * @param string $annotation_id
     * @param string $space_url_suffix
     * @return string|null 'like', 'dislike', or null
     */
    public static function getUserVote($user_id, $paper_id, $annotation_id, $space_url_suffix)
    {
        $vote = self::find()
            ->where([
                'user_id' => $user_id,
                'paper_id' => $paper_id,
                'annotation_id' => $annotation_id,
                'space_url_suffix' => $space_url_suffix,
            ])
            ->one();
        
        return $vote ? $vote->action : null;
    }

    /**
     * Save or update a vote
     * 
     * @param int $user_id
     * @param int $paper_id
     * @param string $annotation_id
     * @param string $annotation_name
     * @param string $space_url_suffix
     * @param string $action 'like' or 'dislike'
     * @return bool
     */
    public static function saveVote($user_id, $paper_id, $annotation_id, $annotation_name, $space_url_suffix, $action)
    {
        $vote = self::find()
            ->where([
                'user_id' => $user_id,
                'paper_id' => $paper_id,
                'annotation_id' => $annotation_id,
                'space_url_suffix' => $space_url_suffix,
            ])
            ->one();
        
        if ($vote) {
            // Update existing vote
            $vote->action = $action;
            $vote->annotation_name = $annotation_name;
            return $vote->save(false);
        } else {
            // Create new vote
            $vote = new self();
            $vote->user_id = $user_id;
            $vote->paper_id = $paper_id;
            $vote->annotation_id = $annotation_id;
            $vote->annotation_name = $annotation_name;
            $vote->space_url_suffix = $space_url_suffix;
            $vote->action = $action;
            return $vote->save(false);
        }
    }

    /**
     * Delete a vote
     * 
     * @param int $user_id
     * @param int $paper_id
     * @param string $annotation_id
     * @param string $space_url_suffix
     * @return bool
     */
    public static function deleteVote($user_id, $paper_id, $annotation_id, $space_url_suffix)
    {
        return self::deleteAll([
            'user_id' => $user_id,
            'paper_id' => $paper_id,
            'annotation_id' => $annotation_id,
            'space_url_suffix' => $space_url_suffix,
        ]) > 0;
    }
}

