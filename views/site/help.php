<?php


use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'BIP! Services - Help';
?>

<div class="container site-about">

	<a href="#create-bip-services-account" class="no-underline"><h3 id="create-bip-services-account">Create a BIP! Services account</h3></a>
	
	<p class='help-text'>
		Click the "<?= Html::a('Login', Url::to(['site/login']), [ 'class' => 'main-green' ]) ?>" button in the top-right of the BIP! Services website. 
	</p>

	<?= Html::img("@web/img/help/help-1.png", ['alt' => 'Login', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>
	
	<p class='help-text'>
		Click the "<?= Html::a('Sign up', Url::to(['site/signup']), [ 'class' => 'main-green' ]) ?>" option that exists in the Login page.
	</p>

	<?= Html::img("@web/img/help/help-2.png", ['alt' => 'Sign up', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	<p class='help-text'>
		Fill out the required fields in the form.
	</p>

	<?= Html::img("@web/img/help/help-3.png", ['alt' => 'Sign up form', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	<p class='help-text'>
		Congratulations! You have created a BIP! Services account!
	</p>

	<?= Html::img("@web/img/help/help-4.png", ['alt' => 'Sign up form', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	<a href="#create-bip-scholar-academic-profile" class="no-underline"><h3 id="create-bip-scholar-academic-profile">Create a BIP! Scholar academic profile</h3></a>

	<p class='help-text'>
		Go to the main <?= Html::a('BIP! Scholar', Url::to(['scholar/index']), [ 'class' => 'main-green' ]) ?> page and click the "<?= Html::a('Create Profile', Url::to(['scholar/profile']), [ 'class' => 'main-green' ]) ?>" button. 
	</p>

	<?= Html::img("@web/img/help/help-5.png", ['alt' => 'Create Scholar profile', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>
	
	<p class='help-text'>
		On the new page, click "Link with your ORCID" and follow the ORCID system's instructions to grant permission for syncing your ORCID account with your BIP! Scholar profile.
	</p>

	<?= Html::img("@web/img/help/help-6.png", ['alt' => 'Create Scholar profile', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	<p class='help-text'>
		That's it! Your BIP! Scholar profile is now created and includes all public records from your ORCID profile. By default, all profiles remain private for self-monitoring unless you choose to make them public for sharing with everyone.
	</p>

</div>
