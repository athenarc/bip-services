<?php
/* 
 * Class to check for previous url and return a message
 */
namespace app\models;
use yii\helpers\Url;
use Yii;


class PreviousUrlChecker
{
    /*
     * Depending on the order on which sites were visited, we can return a message in the forms
     */
    public static function msg_based_on_previous_url()
    {
        $prev_url = Url::previous();
        $current_url = Url::current();
        /*
         * If we go to login page from password reset page, we should be notified that it worked!
         */
        if(strpos($prev_url, 'site/successupdate') !== false && strpos($current_url, 'site/login') !== false)
        {
            return "Password successfully reset!";
        }
        /*
         * If we get to login page from sendmail page, we should be notified we will get mail
         */
        else if(strpos($prev_url, 'site/sendmail') !== false)
        {
            return 'You will receive an email for your password reset. (Please, also check your spam folder)';
        }
        /*
         * If we get from password reset form to itself, there must have been an error updating passowrd
         */
        else if((strpos($prev_url, 'site/passreset') !== false && strpos($current_url, 'site/passreset') !== false))
        {
            return "Could not reset password, please try again!";
        }
        /*
         * Default case should return no message
         */
        else if((strpos($prev_url, 'likeunavailable') !== false && Yii::$app->user->isGuest))
        {
            return "You need to log in or sign up to bookmark papers!";//"Please log in or sign up to 'like' papers!";
        }
        else
        {
            return '';
        }
    }
}

