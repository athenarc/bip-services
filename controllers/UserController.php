<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class UserController extends Controller {
    public function actionIndex() {
        $model = new User();

        return $this->render('index', ['model' => $model]);
    }

    public function actionUpdateSetting() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $setting_name = Yii::$app->request->post('settingName');
        $setting_value = Yii::$app->request->post('settingValue');
        $allowed_settings = ['keyword_relevance', 'ai_features'];

        if (! in_array($setting_name, $allowed_settings, true)) {
            return ['success' => false, 'error' => 'Invalid setting name.'];
        }

        $user = User::findOne(Yii::$app->user->id);

        if ($user) {
            $user[$setting_name] = $setting_value;

            if ($user->save()) {
                return ['success' => true];
            }

            return ['success' => false, 'error' => 'Failed to save user data.'];
        }

        return ['success' => false, 'error' => 'User not found.'];
    }

    public function actionGenerateApiToken() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = User::findOne(Yii::$app->user->id);
        if (! $user) {
            return ['success' => false, 'error' => 'User not found.'];
        }

        $token = bin2hex(random_bytes(25)); // 50-char token fits auth_token varchar(50)
        $user->auth_token = $token;

        if ($user->save(false, ['auth_token'])) {
            return ['success' => true, 'token' => $token];
        }

        return ['success' => false, 'error' => 'Failed to generate token.'];
    }
}
