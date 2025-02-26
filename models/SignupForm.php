<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;
use yii\helpers\Html;

/**
 * SignupForm is the model behind the sign up form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class SignupForm extends Model
{
    public $username;
    public $password;
    public $email;
    public $rememberMe = true;
    public $postMsg;
    public $captcha;

    public $auth_provider;
    public $auth_id;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            //Username Rules
            ['username', 'trim'],
            ['username', 'unique', 'targetClass' => 'app\models\User', 'message' => 'This username has already been taken.'],
            // username and password are both required
            ['username', 'required'],
            //ADD USERNAME MAX LENGTH
            ['username', 'string', 'max' => 30],
            //Email rules
            ['email', 'required', 'message' => 'Email cannot be blank'],
            ['email', 'email'],
            ['email', 'string', 'max' => 50],
            ['email', 'unique', 'targetClass' => 'app\models\User', 'message' => 'Email already exists.'],
            //Password rules
            ['password', 'required', 'when' => function($model) {
                return empty($model->auth_id);
            }, 'whenClient' => "function (attribute, value) {
                return !$('#" . Html::getInputId($this, 'auth_id') . "').val();
            }"],
            ['password', 'string', 'min' => 6],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],

            ['captcha', 'required'],
            ['captcha', 'captcha'],
            
            ['auth_id', 'string'],
            ['auth_provider', 'string'],
            
            ['auth_id', 'validateAuthId'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'captcha' => 'Verification Code',
            'auth_provider' => 'Provider',
            'auth_id' => 'Identifier',
        ];
    }

    public function validateAuthId($attribute, $params) {
        $session_auth_id = Yii::$app->session->get('auth_id');
        if ($this->auth_id !== $session_auth_id) {
            $this->addError($attribute, 'Invalid ID: Please authenticate through ' . $this->auth_provider . '.');
        }
    }

    /**
     * Validates the username
     * This method serves as the inline validation for username.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUsername($attribute, $params)
    {
        if (!$this->hasErrors()) 
        {
            $user = $this->getUser();

            if (!$user->validateUsername($this->username)) 
            {
                $this->addError($attribute, 'Username already exists!');
            }
        }
    }
    
    /*
     * Sign up the user
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        // Create new user record and insert it        
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->auth_provider = $this->auth_provider;
        $user->auth_id = $this->auth_id;
        $user->setPassword($this->password);

        // Save the user
        if ($user->save()) {
            return $user; // Return the saved user object
        }

        return null;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) 
        {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}


