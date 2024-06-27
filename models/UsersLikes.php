<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "users_likes".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $paper_id
 * @property boolean $showit
 * @property integer $reading_status
 */
class UsersLikes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users_likes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'paper_id'], 'required'],
            [['user_id', 'paper_id', 'folder_id', 'reading_status'], 'integer'],
            //Rule required to recognise attribute
            ['showit', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'paper_id' => 'Paper ID',
            'showit'  => 'Display'
        ];
    }

    /*
     * Action to run before validating the active record.
     * This turns the possible integer value of 'showit'
     * column into a boolean value that is required by
     * the validation rules
     */
    public function beforeValidate()
    {
        if(is_int($this->showit))
        {
            $this->showit = boolval($this->showit);
        }
        //Return true, otherwise the validation won't happen,
        //and the updates will fail
        return true;
    }

    /*
     * Declare a relation to user_likes (there will be an article id in the likes)
     */
    public function getArticle()
    {
        return $this->hasOne(Article::className(), ['internal_id' => 'paper_id']);
    }

    /*
     * Find the article with most likes
     */
    public static function getMostLikedPaper()
    {
        /*echo UsersLikes::find()
                ->select(['*' , 'likes' => 'count(*)'])
                ->where(['showit'=>true])
                ->groupBy('paper_id')
                ->orderBy(['likes' => SORT_DESC])->createCommand()->rawSql; */

        return UsersLikes::find()
                ->select(['*' , 'likes' => 'count(*)'])
                ->where(['showit'=>true])
                ->groupBy('paper_id')
                ->orderBy(['likes' => SORT_DESC])
                ->asArray()->one();
    }

    /*
     * Find total likes and percentage of likes for certain paper
     *
     */
    public static function getArticleLikePercentage($internal_id)
    {
        $total_likes = (new \yii\db\Query())
                    ->select('paper_id')
                    ->from('users_likes')->count();

        $paper_likes = UsersLikes::find()->where(['showit' => true, 'paper_id' => $internal_id])->count();

        //Fix in case of no likes
        if($total_likes == 0)
        {
            $total_likes = 1;
        }

        return ($paper_likes/$total_likes);
    }

    public static function UserHasLikes($user_id) {

        $has_likes = UsersLikes::find()->where(['user_id' => $user_id, 'showit' => true])->exists();

        return ($has_likes);
    }

    public static function countUserLikes($user_id) {
        return (new \yii\db\Query())
            ->select(['users_likes.*', 'pmc_paper.*'])
            ->from('users_likes')
            ->where(['users_likes.user_id' => $user_id])
            ->andWhere(['users_likes.showit' => true])
            ->count();
    }

    public static function getUserPapersInFolder($user_id, $folder_id) {
        return (new \yii\db\Query())
                ->select(['users_likes.*', 'pmc_paper.*', 'GROUP_CONCAT(tags.name ORDER BY tags_to_papers.timestamp ASC) AS tags'])
                ->from('users_likes')
                ->innerJoin('pmc_paper', 'pmc_paper.internal_id = users_likes.paper_id')
                ->leftJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id 
                        AND tags_to_papers.user_id = ' . $user_id)        
                ->leftJoin('tags', 'tags.id = tags_to_papers.tag_id')
                ->where(['users_likes.user_id' => $user_id])
                ->andWhere(['users_likes.showit' => true])
                ->andWhere(['users_likes.folder_id' => $folder_id])
                ->groupBy('internal_id');
    }

    public static function updateTableValue($user_id, $paper_id, $column_name, $value) {

        $exists = UsersLikes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id, 'showit' => true])->exists();
        if($exists)
        {
            $user_like = UsersLikes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id, 'showit' => true])->one();
            $user_like[$column_name] = $value;
            $user_like->update();

        } else {
            // bookmark doesn't exist
            throw new \yii\base\Exception;
        }
        return;
    }
}
