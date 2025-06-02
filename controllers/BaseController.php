<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;

            // Only update once per day
            if ($user->last_visited === null || date('Y-m-d', strtotime($user->last_visited)) < date('Y-m-d'))
            {
                $user->last_visited = date('Y-m-d H:i:s');
                $user->save(false);
            }
        }

        return true;
    }
}
