<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    public $oldPassword;
    public $newPassword;
    public $confirmPassword;

    public function rules()
    {
        return [
            [['oldPassword', 'newPassword', 'confirmPassword'], 'required'],
            ['confirmPassword', 'compare', 'compareAttribute' => 'newPassword', 'message' => "Passwords don't match."],
        ];
    }

    public function changePassword($user)
    {
        if (!Yii::$app->security->validatePassword($this->oldPassword, $user->password)) {
            $this->addError('oldPassword', 'Incorrect current password.');
            return false;
        }

        $user->setPassword($this->newPassword);
        return $user->save();
    }

    public function attributeLabels()
    {
        return [
            'oldPassword' => 'Current Password',
        ];
    }
}
