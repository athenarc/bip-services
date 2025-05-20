<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\controllers\BaseController;
use app\models\User;


class SpacesController extends BaseController

{

    public function actionIndex() {

        $user_id = Yii::$app->user->id;
        $user = User::findIdentity($user_id);

        return $this->render('spaces', [
            'user' => $user
        ]);
    }

}
