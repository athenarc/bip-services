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

    public function actionUpdateKeywordRelevance()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
        $keywordRelevance = Yii::$app->request->post('keyword_relevance');
        $user = User::findOne(Yii::$app->user->id);
    
        if ($user) {
            $user->keyword_relevance = $keywordRelevance;
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
