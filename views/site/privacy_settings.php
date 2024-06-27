<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$title = 'Privacy Settings';
$this->title = 'BIP! Finder - Privacy Settings';

$this->registerJsFile('@web/js/third-party/google_analytics/analyticsOptOut.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/third-party/google_analytics/analyticsOptOut.css');
?>
<div class="container site-about">
    <h1><?= Html::encode($title) ?></h1>

    <h3>Matomo Web Analytics</h3>

	<iframe
	 style="border: 0; height: 100%; width: 100%;"
	 src="https://genomics-lab.fleming.gr/piwik/index.php?module=CoreAdminHome&action=optOut&language=en&fontSize=&fontFamily='Arial'"
	></iframe>

	<h3>Google Analytics</h3>

	<?php ActiveForm::begin($form_params); ?>
	<div class="opt-out-msg">
    	You may choose not to have Google analytics Enabled, to avoid the aggregation and analysis of data collected on this website.
		<br />To make that choice, please click below to receive an opt-out cookie.</br> 
	<?= Html::checkBox('analytics_opt_out_checkbox', $boxValue, ['id'=>"analytics_opt_out_checkbox"]) ?>
	<?= Html::hiddenInput('analytics_opt_out', $boxValue, ['id'=>"analytics_opt_out"]) ?>
	<?= Html::label($boxLabel) ?>
    </div>

	<?php ActiveForm::end(); ?>
	<p class="help-text">You can also review our <a href="<?= Url::toRoute(['site/data-policy']) ?>" class="main-green">Privacy and Cookie Policy</a>.</p>
</div>
