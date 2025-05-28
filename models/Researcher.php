<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\User;


class Researcher extends ActiveRecord {

    // Method do connect class to db table
    public static function tableName() {
        return '{{researchers}}';
    }

    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function findByOrcid($orcid) {
        return static::findOne(['orcid' => $orcid]);
    }

    public static function add($user_id, $orcid, $access_token, $name) {
        
        $researcher = new Researcher();

        $researcher->user_id = $user_id;
        $researcher->orcid = $orcid;
        $researcher->access_token = $access_token;
        $researcher->name = $name;

        $researcher->save();

        return $researcher;
    }

    public static function updatePublicProfile($user_id, $is_public) {
        $researcher = Researcher::findOne([ 'user_id' => $user_id ]);
        if (!$researcher) {
            throw new \yii\base\Exception;
        }

        $researcher->is_public = $is_public;
        $researcher->save();

        return $researcher;
    }

    public static function findPublicByName($name)
    {
        return self::find()
            ->where(['like', 'name', $name])
            ->andWhere(['is_public' => 1])
            ->one();
    }

}