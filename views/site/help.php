<?php


use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'BIP! Services - Help';
?>

<div class="container site-about">
    <!-- <h1><?= Html::encode($this->title) ?></h1> -->

	<h3>Search for research works</h3>
	<p class='help-text'>
	You can search for research works relevant to a subject of interest. Just enter a set of 
	keywords that describes the subject in BIP's search box and press "Find!". The results 
	will appear below the search box. 
	</p>
	<?= Html::img("@web/img/1.gif", ['alt' => 'Search example', 'class' => 'img-responsive screenshot center-block', 'width' => 485, 'height' => 265]) ?>
	<p class='help-text'>
	The results can be ordered based on each paper's 
	<span class="green-bip" role="button" data-toggle="popover" data-placement="auto" title="<b>Popularity</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= HTML::encode($indicators['Popularity']) ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Popularity']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>">popularity</span>,
	<span class="green-bip" role="button" data-toggle="popover" data-placement="auto" title="<b>Influence</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= HTML::encode($indicators['Influence']) ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Influence']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>">influence</span>,
	<span class="green-bip" role="button" data-toggle="popover" data-placement="auto" title="<b>Impulse</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= HTML::encode($indicators['Impulse']) ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Impulse']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>">impulse</span>
	or publication year.
	You can select want type of ordering suits better 
	your needs using the corresponding radio button (located below BIP's search box). 
	</p>
	<?= Html::img("@web/img/2.gif", ['alt' => 'Ranking example', 'class'=>'img-responsive screenshot center-block', 'width' => 485, 'height' => 265]) ?>

	
	<h3>Compare research works</h3>
	<p class='help-text'>
	BiP! finder supports comparisons between selected research works. You can select 2-4 research works for comparison
	just by clicking on the corresponding row in the search results. 
	After finalizing your selection,
	just click on the orange button appearing at the top right corner of the page. A new page,
	containing a radar chart, will appear. 
	</p>
	<?= Html::img("@web/img/3.gif", ['alt' => 'Comparison example', 'class'=>'img-responsive screenshot center-block', 'width' => 485, 'height' => 265]) ?>
	
	<h3>Add research works to your readings</h3>
	<p class='help-text'>
	In case you discover interesting research works, you can save them in your readings 
	(you have to login first to have access to your readings page - singing up is completely free). 
	You can access your readings just by clicking on the "Scholar - Readings" menu item, at the top of 
	the webpage. 
	You can also categorize your readings in different reading lists (and publicly share them) 
	based on (combinations of) the different facets available (i.e. tags, reading status, availability and work type).
	Finally, you can easily remove a saved reading anytime you want from your readings page.
	</p>
	<?= Html::img("@web/img/4.gif", ['alt' => 'Readings example', 'class'=>'img-responsive screenshot center-block', 'width' => 485, 'height' => 265]) ?>

	<h3>Share your scholar profile</h3>
	<p class='help-text'>
	You can set up your BIP! Scholar Profile that summarizes your research career, taking into account the latest guidlines for fair research assessment.
	You can access your scholar profile by clicking on the "Scholar - Profile" menu item, at the top of the webpage. 
	Note that you have to sign in first (sign up is free) and link your BIP! Scholar Profile with your ORCiD account.
	</p>
	<?= Html::img("@web/img/5.gif", ['alt' => 'Scholar profile example', 'class'=>'img-responsive screenshot center-block', 'width' => 485, 'height' => 265]) ?>

</div>
