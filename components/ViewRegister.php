<?php

namespace app\components;

use Yii;
use yii\base\Component; 
use app\models\GuestViews;
use app\models\UserViews;

/**
 * Implementation of view registration procedure for guests and users
 *
 * @author Hlias
 */
class ViewRegister extends Component 
{
    /**
     * Register Guest view
     * 
     * @author Hlias
     */
    public function registerGuestView($ip, $paper_id)
    {
        if(!GuestViews::find()->where(['guest_ip' => $ip])->andWhere(['paper_id' => $paper_id])->exists())
        {
            $guest_view = new GuestViews();
            $guest_view->guest_ip = $ip;
            $guest_view->paper_id = $paper_id;
            $guest_view->save();
        }
    } 
    /**
     * Register User View
     * 
     * @author Hlias
     */    
    public function registerUserView($user_id, $paper_id)
    {
        if(!UserViews::find()->where(['user_id' => $user_id])->andWhere(['paper_id' => $paper_id])->exists())
        {
            $user_view = new UserViews();
            $user_view->user_id = $user_id;
            $user_view->paper_id = $paper_id;
            $user_view->save();          
        }        
    }        
}