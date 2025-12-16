<?php

namespace app\controllers;

use app\models\User;
use Yii;

class SpacesController extends BaseController {
    public function actionIndex() {
        $user_id = Yii::$app->user->id;
        $user = User::findIdentity($user_id);

        return $this->render('spaces', [
            'user' => $user
        ]);
    }
}
