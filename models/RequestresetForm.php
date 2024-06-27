<?php


namespace app\models;

use yii\base\Model;
use app\models\User;

/**
 * The model behind the email form requesting password reset. 
 *
 * @author Hlias
 */
class RequestresetForm extends Model
{
	public $email;
        
        
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
	 *
	 * @return Array containing the validation rules.
	 *
	 * @author Hlias
	 */
	public function rules()
	{
		return 
                [
                       //Set a valid email address
                       ['email', 'email', 'message' => 'Not a valid email address'],
                       ['email', 'required', 'message' => 'Enter email address to receive password reset link'],
                       ['email', 'validateEmail']
                ];
	}    
        
        /*
	 * @return Array containing the form elements labels.
	 *
	 * @author Hlias
	 */
	public function attributeLabels()
	{
		return ['email'=>'Email'];
	}
        
        /*
         * Email validator
         * 
         * @return add error if there is no user with this mail.
         * 
         * @author Hlias
         */
        public function validateEmail($attribute, $params)
        {
            /*
             * check if the user exists in the database or not
             */
            if(!User::find()->where(['email' => $this->email])->exists())
            {
                $this->addError($attribute, 'No user with this email exists!');
            }
        }
}

   
