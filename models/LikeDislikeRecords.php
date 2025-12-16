<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "like_dislike_records".
 *
 * @property int $id
 * @property int $user_id
 * @property string $space_url_suffix
 * @property string|null $query
 * @property string|null $ordering
 * @property int $paper_id
 * @property int|null $paper_rank
 * @property string $action
 */
class LikeDislikeRecords extends ActiveRecord {
    public static function tableName() {
        return 'like_dislike_records';
    }

    public function rules() {
        return [
            [['user_id', 'space_url_suffix', 'paper_id', 'action'], 'required'],
            [['user_id', 'paper_id', 'paper_rank'], 'integer'],
            [['query'], 'string'],
            [['space_url_suffix'], 'string', 'max' => 255],
            [['ordering'], 'string', 'max' => 50],
            [['action'], 'in', 'range' => ['like', 'dislike']],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'space_url_suffix' => 'Space URL Suffix',
            'query' => 'Query',
            'ordering' => 'Ordering',
            'paper_id' => 'Paper ID',
            'paper_rank' => 'Paper Rank',
            'action' => 'Action',
        ];
    }

    /**
     * Get vote counts for a paper in a space.
     *
     * @param int $paper_id
     * @param string $space_url_suffix
     * @return array ['like_count' => int, 'dislike_count' => int]
     */
    public static function getVoteCounts($paper_id, $space_url_suffix) {
        $rows = self::find()
            ->select(['action', 'cnt' => 'COUNT(*)'])
            ->where([
                'paper_id' => $paper_id,
                'space_url_suffix' => $space_url_suffix,
            ])
            ->groupBy('action')
            ->asArray()
            ->all();

        $like_count = 0;
        $dislike_count = 0;

        foreach ($rows as $row) {
            if ($row['action'] === 'like') {
                $like_count = (int) $row['cnt'];
            } elseif ($row['action'] === 'dislike') {
                $dislike_count = (int) $row['cnt'];
            }
        }

        return [
            'like_count' => $like_count,
            'dislike_count' => $dislike_count,
        ];
    }

    /**
     * Get user's vote for a paper in a space.
     *
     * @param int $user_id
     * @param int $paper_id
     * @param string $space_url_suffix
     * @return string|null 'like', 'dislike', or null
     */
    public static function getUserVote($user_id, $paper_id, $space_url_suffix) {
        $vote = self::find()
            ->where([
                'user_id' => $user_id,
                'paper_id' => $paper_id,
                'space_url_suffix' => $space_url_suffix,
            ])
            ->one();

        return $vote ? $vote->action : null;
    }

    /**
     * Get user's votes for multiple papers in a space (batch query).
     *
     * @param int $user_id
     * @param array $paper_ids Array of paper IDs
     * @param string $space_url_suffix
     * @return array Associative array with paper_id as key and 'like'/'dislike' as value
     */
    public static function getUserVotesBatch($user_id, $paper_ids, $space_url_suffix) {
        if (empty($paper_ids) || ! is_array($paper_ids)) {
            return [];
        }

        $votes = self::find()
            ->where([
                'user_id' => $user_id,
                'paper_id' => $paper_ids,
                'space_url_suffix' => $space_url_suffix,
            ])
            ->all();

        $result = [];

        foreach ($votes as $vote) {
            $result[$vote->paper_id] = $vote->action;
        }

        return $result;
    }

    /**
     * Save or update a vote.
     *
     * @param int $user_id
     * @param int $paper_id
     * @param string $space_url_suffix
     * @param string $action 'like' or 'dislike'
     * @param string|null $query
     * @param string|null $ordering
     * @param int|null $paper_rank
     * @return bool
     */
    public static function saveVote($user_id, $paper_id, $space_url_suffix, $action, $query = null, $ordering = null, $paper_rank = null) {
        $vote = self::find()
            ->where([
                'user_id' => $user_id,
                'paper_id' => $paper_id,
                'space_url_suffix' => $space_url_suffix,
            ])
            ->one();

        if ($vote) {
            // Update existing vote
            $vote->action = $action;
            $vote->query = $query;
            $vote->ordering = $ordering;
            $vote->paper_rank = $paper_rank;

            return $vote->save();
        }
        // Create new vote
        $vote = new self();
        $vote->user_id = $user_id;
        $vote->paper_id = $paper_id;
        $vote->space_url_suffix = $space_url_suffix;
        $vote->action = $action;
        $vote->query = $query;
        $vote->ordering = $ordering;
        $vote->paper_rank = $paper_rank;

        return $vote->save();
    }

    /**
     * Delete a vote.
     *
     * @param int $user_id
     * @param int $paper_id
     * @param string $space_url_suffix
     * @return bool
     */
    public static function deleteVote($user_id, $paper_id, $space_url_suffix) {
        return self::deleteAll([
            'user_id' => $user_id,
            'paper_id' => $paper_id,
            'space_url_suffix' => $space_url_suffix,
        ]) > 0;
    }
}
