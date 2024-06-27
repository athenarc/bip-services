<?php

namespace app\components;
use yii\helpers\Html;
use app\models\Application;
use yii\web\Cookis;
use yii\helpers\Url;
use Yii;

/*
 * Yii Helper for adding Matomo to the website 
 */
class Matomo
{
	public static function checkAndAdd() {
		// do not include matomo script on test server
		if (gethostname() == 'andrea')
			return;   		 
    				
		return Html::jsFile('@web/js/matomo.js');
	}

	public static function checkAndAddNonScript() {
		// do not include matomo script on test server
		if (gethostname() == 'andrea')
			return;   		 

		return '<noscript><p><img src="//genomics-lab.fleming.gr/piwik/piwik.php?idsite=7&rec=1" style="border:0;" alt="" /></p></noscript>';
	}
}
