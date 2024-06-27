<?php

namespace app\models;

use yii\base\Model;
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\User;

/*
 * Form model for password reset!
 * 
 * @author: Hlias
 */

class PassresetForm extends Model
{
        public $username;
        public $newPass;
        //User reset token, must be validated - must be correct and not too old
        public $token;
        //
        public $postMsg;
        
        /*
         * Don't use a name for the form (avoid array params in url)
         * 
	 * @author Ilias Kanellos
	 */
        public function formName()
        {
            return '';
        } 
        
        /*
	 * @return Array containing the validation rules.
	 *
	 * @author Hlias
	 */
	public function rules()
	{
		return 
                [
                    ['username', 'trim'],
                    ['username', 'required', 'message' => 'Please enter your username',
                    //Fire this only when token is set!
                     'when' => function($model){ return (isset($model->token) && $model->token != ''); }],
                    ['username', 'validateUsername',
                    //Fire this only when token is set!
                     'when' => function($model){ return (isset($model->token) && $model->token != ''); }],
                    ['newPass', 'trim'],
                    ['newPass', 'required', 'message' => 'You must specify your new password!',
                    //Fire this only when token is set!
                     'when' => function($model){ return (isset($model->token) && $model->token != ''); }],
                    ['newPass', 'string', 'min' => 6,
                    //Fire this only when token is set!
                     'when' => function($model){ return (isset($model->token) && $model->token != ''); }],
                    ['token', 'required', 'message' => 'Not authorized for password reset!'],
                    ['token', 'validateResettoken']
                ];
	}
        
        /*
	 * @return Array containing the form elements labels.
	 *
	 * @author Hlias
	 */
	public function attributeLabels()
	{
		return ['username'=>'Username', 'newPass' => 'New Pass'];
	}        
        
        /*
         * Username validator
         * 
         * @return add error if there is no user with this mail.
         * 
         * @author Hlias
         */
        public function validateUsername($attribute, $params)
        {
            /*
             * check if the user exists in the database or not
             */
            if(!User::find()->where(['username' => $this->username])->exists())
            {
                $this->addError($attribute, 'This user does not exist!');
            }
        }
 
        /*
         * Reset token validator
         * 
         * @return add error if there is no user with this mail.
         * 
         * @author Hlias
         */
        public function validateResettoken($attribute, $params)
        {
            /*
             * check if the user exists in the database or not
             */
            if(!User::find()->where(['username' => $this->username])->exists())
            {
                return;
            }
            else if(!User::find()->where(['username' => $this->username, 'reset_key' => $this->token])->exists())
            {
                $this->addError($attribute, 'This user has not requested a password reset!');
            }
            else
            {
                //Get user
                $user = User::find()->where(['username' => $this->username, 'reset_key' => $this->token])->one();
                if(User::find()->where(['username' => $this->username, 'reset_key' => $this->token])
                   ->andWhere(['>', 'NOW()', $user->expires])->exists())
                {
                    $this->addError($attribute, 'The reset token for this user has expired. Please ' . Html::a('request a new one', Url::to(['site/requestreset'], true), ['class' => 'text-success']));
                }
            }
        }    
        
}
   