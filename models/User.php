<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Yii;
use app\models\Researcher;


class User extends ActiveRecord implements \yii\web\IdentityInterface
{
    /*
    private $id;
    private $username;
    private $email;
    private $password;
    private $authKey;
    private $accessToken;
    private $reset_key;
    private $expires;
    */

    /*
     * Method do connect class to db table
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @Hlias: method to return db record that corresponds to user id
     */
    public static function findIdentity($id)
    {
        /*
         * This is code by @Hlias to get db record of user.
         * (Shamelessly copying from http://www.yiiframework.com/doc-2.0/guide-security-authentication.html)
         */
        return static::findOne($id);
        
    }

    public function getResearcher() {
        return $this->hasOne(Researcher::class, ['user_id' => 'id']);
    }
    
    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }
    
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function setPassword($password)
    {
        $this->password = \Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function getKeywordRelevance() {
        if ($this->keyword_relevance == 0) {
            return "low";
        }
        return "high";
    }
    
    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        /*
         * create password hash
         */
        $hash = Yii::$app->security->generatePasswordHash($password);
        /*
         * Compare user pass to hash created from password entered
         */
        $user_pass = $this->password;
        return Yii::$app->getSecurity()->validatePassword($password, $user_pass);
    }
    
    
    /*
     * @autho: Hlias
     * Generate authentication key for each user before inserting into database
     */
    public function beforeSave($insert)
    {
        /*
         * Create an authentication key before insertion
         */
        if (parent::beforeSave($insert)) 
        {
            if ($this->isNewRecord) 
            {
                $this->authKey = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }

    // used to restrict access in some api routes
    public static function validateAuthToken($auth_token) {
        if (!$auth_token) {
            throw new \yii\base\Exception;
        }

        return (new \yii\db\Query())
            ->select("id")
            ->from('users')
            ->where(['auth_token' => $auth_token])
            ->exists();
    }
    /**
     * Check if the user has a scholar profile.
     *
     * @return bool
     */
    public function getHasScholarProfile()
    {
        // Replace 'scholars' with your actual table name if different
        return Scholar::find()->where(['user_id' => $this->id])->exists();
    }
}
