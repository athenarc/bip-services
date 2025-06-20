<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\User;

class UserController extends Controller
{
    public function actionIndex()
    {
        $model = new User();
        return $this->render('index', ['model' => $model]);
    }

    public function actionUpdateSetting() {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        $setting_name = Yii::$app->request->post('settingName');
        $setting_value = Yii::$app->request->post('settingValue');

        $user = User::findOne(Yii::$app->user->id);
    
        if ($user) {
            $user[$setting_name] = $setting_value;
            if ($user->save()) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Failed to save user data.'];
            }
        } else {
            return ['success' => false, 'error' => 'User not found.'];
        }
    }
}
