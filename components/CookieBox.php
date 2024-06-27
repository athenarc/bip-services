<?php

namespace app\components;
use yii\helpers\Html;
use app\models\Application;
use yii\web\Cookis;
use yii\helpers\Url;
use Yii;

/*
 * Yii Helper for creating a message box on the side of the screen asking the user to accept cookies
 *
 * @author Kostis Zagganas (First Version: September 2018)
 */
class CookieBox
{
	/*
	 * Helper function creating the box
	 *
	 */
	public static function show()
	{
		$cssFile='@web/css/components/cookieBox.css';
		$jsFile='@web/js/components/cookieBox.js';

		$cookies = Yii::$app->request->cookies;

		$ajaxLink=Url::toRoute(['site/accept-cookies']);
		$terms=Url::toRoute(['site/data-policy']);

		if (!isset($cookies['BipCookiesAccept'])) 
		{
    				
			echo Html::cssFile($cssFile);
			echo Html::jsFile($jsFile, ['depends' => 'yii\web\JqueryAsset']);

			echo '<div class="cookie-container">';
			echo '<div class="cookie-text">We have placed cookies on your device to help make this website and the services we offer better. By using this site, you agree to the use of cookies. <a href="' . $terms . '" class="main-green">Learn more</a></div>';
			echo '<center><div class="btn btn-custom-color" onclick="cookieAcceptClick(\'' . $ajaxLink . '\');">I accept</div></center>';
			echo '</div>';
		}
	}

}
