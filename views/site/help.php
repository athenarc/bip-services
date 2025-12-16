<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->registerJsFile('@web/js/scrollToAnchor.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->registerCss('
	.site-about h4 {
		color: #000000;
	}
');

$this->title = 'BIP! Services - Help';
?>

<div class="container site-about">

	<a href="#create-bip-services-account" class="no-underline"><h3 id="create-bip-services-account">Create a BIP! Services account</h3></a>
	
	<p class='help-text'>
		Click the "<?= Html::a('Login', Url::to(['site/login']), ['class' => 'main-green']) ?>" button in the top-right of the BIP! Services website. 
	</p>

	<?= Html::img('@web/img/help/help-1.png', ['alt' => 'Login', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>
	
	<p class='help-text'>
		Click the "<?= Html::a('Sign up', Url::to(['site/signup']), ['class' => 'main-green']) ?>" option that exists in the Login page.
	</p>

	<?= Html::img('@web/img/help/help-2.png', ['alt' => 'Sign up', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	<p class='help-text'>
		Fill out the required fields in the form.
	</p>

	<?= Html::img('@web/img/help/help-3.png', ['alt' => 'Sign up form', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	<p class='help-text'>
		Congratulations! You have created a BIP! Services account!
	</p>

	<?= Html::img('@web/img/help/help-4.png', ['alt' => 'Sign up form', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	<a href="#bip-scholar" class="no-underline"><h3 id="bip-scholar">BIP! Scholar</h4></a>

	<a href="#create-bip-scholar-academic-profile" class="no-underline"><h4 id="create-bip-scholar-academic-profile">Create a BIP! Scholar academic profile</h4></a>

	<p class='help-text'>
		Go to the main <?= Html::a('BIP! Scholar', Url::to(['scholar/index']), ['class' => 'main-green']) ?> page and click the "<?= Html::a('Create Profile', Url::to(['scholar/profile']), ['class' => 'main-green']) ?>" button. 
	</p>

	<?= Html::img('@web/img/help/help-5.png', ['alt' => 'Create Scholar profile', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>
	
	<p class='help-text'>
		On the new page, click "Link with your ORCID" and follow the ORCID system's instructions to grant permission for syncing your ORCID account with your BIP! Scholar profile.
	</p>

	<?= Html::img('@web/img/help/help-6.png', ['alt' => 'Create Scholar profile', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	<p class='help-text'>
		That's it! Your BIP! Scholar profile is now created and includes all public records from your ORCID profile. By default, all profiles remain private for self-monitoring unless you choose to make them public for sharing with everyone.
	</p>


	<a href="#bip-finder" class="no-underline"><h3 id="bip-finder">BIP! Finder</h3></a>

	<a href="#bip-finder-in-a-nutshell" class="no-underline"><h4 id="bip-finder-in-a-nutshell">In a nutshell</h4></a>

	<p class='help-text'>
		BIP! Finder is an advanced academic search engine that allows literature exploration and scientific knowledge discovery. It is built upon the OpenAIRE Graph, a comprehensive knowledge base of scientific publications and research outputs.
	</p>

	<a href="#bip-finder-search" class="no-underline"><h4 id="bip-finder-search">Search</h4></a>

	<p class='help-text'>
		The environment offers advanced search functionalities for research products (publications, datasets, software, etc) within the domain of interest:
			<ul class="help-text">
				<li>End-users can use keywords to search for a topic of interest.
					<ul>
						<li>Operators AND, OR, and NOT (written in capital letters): Can be used to combine the keywords according to the desired query semantics. Parentheses can be used to group together keywords combined with these operators.</li>
						<li>Space-separated keywords are considered all together by the system, applying an implicit AND operator.</li>
						<li>Double quotes can be used to search for appearances of the enclosed keywords as an exact phrase match.</li>
					</ul>
				</li>
				<li>They can also refine the search results by applying various filters (e.g., impact indicator classes, type of research products).</li>
				<li>Finally, end-users can change the default ranking scheme for the results. An array of impact indicators (detailed <?= Html::a('here', Url::to(['site/indicators']), ['class' => 'main-green', 'target' => '_blank']) ?>) combined with keyword relevance, are among the options. This can be useful for different use cases. For instance, a student drafting a survey on a topic should prioritise works with high <?= Html::a('influence', Url::to(['site/indicators']) . '#Influence', ['class' => 'main-green', 'target' => '_blank']) ?>, while an experienced researcher who revisits the same topic may prefer to find works with high <?= Html::a('popularity', Url::to(['site/indicators']) . '#Popularity', ['class' => 'main-green', 'target' => '_blank']) ?>).</li>
			</ul>
	</p> 

	<p class='help-text'>
		The search results appear as a list of rows. Each row displays valuable metadata for the respective research product including predetermined annotations from the connected knowledge base. A snapshot of the respective search interface, with the top results displayed, can be found below. 
	</p>

	<?= Html::img('@web/img/help/bip-finder-ui.png', ['alt' => 'BIP! Finder UI', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>

	
	<a href="#bip-finder-ai-summaries" class="no-underline"><h4 id="bip-finder-ai-summaries">AI-generated summaries (for registered users).</h4></a>

	<p class='help-text'>
		Registered BIP! Finder end-users have also the option to ask for AI-generated summaries of the top search results. This can be done by clicking on the “Summarise top results” button placed at the top right corner of the search results section. The summaries are generated by a generative AI model that synthesizes the titles and abstracts from the top-k search results. By default, k is set to 5, but users can adjust this value to regenerate the summary based on a different number of results. Please note that a daily quota limits the number of summaries each user can produce, and a counter displays the remaining allowance.    
	</p>

	<p class='help-text'>
		This feature offers an interesting use case: comparing summaries generated using different ranking schemes. For example, a substantial difference in the narrative between summaries based on <?= Html::a('popularity', Url::to(['site/indicators']) . '#Popularity', ['class' => 'main-green', 'target' => '_blank']) ?> versus <?= Html::a('influence', Url::to(['site/indicators']) . '#Influence', ['class' => 'main-green', 'target' => '_blank']) ?>, ranking could indicate a shift in the prevailing literature's focus over time.
	</p>

	<a href="#bip-spaces" class="no-underline"><h3 id="bip-spaces">BIP! Spaces</h3></a>

	<a href="#bip-spaces-in-a-nutshell" class="no-underline"><h4 id="bip-spaces-in-a-nutshell">In a nutshell</h4></a>

	<p class='help-text'>
		Each BIP! Space is an academic search engine tailored for the needs of a specific organisation or community. More importantly, each space is connected with a knowledge base that encodes annotations related to the domain of interest determined by the organisation or community that owns it. A BIP! Space is created upon request (if you are interested, please send an email at <?= Html::mailto('bip@athenarc.gr', 'bip@athenarc.gr', ['class' => 'main-green']) ?>). 
	</p>

	<a href="#bip-spaces-search" class="no-underline"><h4 id="bip-spaces-search">Search</h4></a>

	<p class='help-text'>
	The environment offers advanced search functionalities for research products (publications, datasets, software, etc) within the domain of interest:
		<ul class="help-text">
			<li>End-users can use keywords to search for a topic of interest.</li>
			<ul>
				<li>Operators AND, OR, and NOT (written in capital letters) can be used to combine the keywords according to the desired query semantics. Parentheses can be used to group together keywords combined with these operators.</li>
				<li>Space-separated keywords are considered all together by the system, applying an implicit AND operator.</li>
				<li>Double quotes can be used to search for appearances of the enclosed keywords as an exact phrase match.</li>
			</ul>
			<li>They can also refine the search results by applying various filters (e.g., impact indicator classes, type of research products).</li>
			<li>Finally, end-users can change the default ranking scheme for the results. An array of impact indicators (detailed <?= Html::a('here', Url::to(['site/indicators']), ['class' => 'main-green', 'target' => '_blank']) ?>) combined with keyword relevance, are among the options. This can be useful for different use cases. For instance, a student drafting a survey on a topic should prioritise works with high <?= Html::a('influence', Url::to(['site/indicators']) . '#Influence', ['class' => 'main-green', 'target' => '_blank']) ?>, while an experienced researcher who revisits the same topic may prefer to find works with high <?= Html::a('popularity', Url::to(['site/indicators']) . '#Popularity', ['class' => 'main-green', 'target' => '_blank']) ?>).</li>
		</ul>
	</p>

	<p class='help-text'>
		The search results appear as a list of rows. Each row displays valuable metadata for the respective research product including predetermined annotations from the connected knowledge base. A snapshot of the respective search interface, with the top results displayed, can be found below. 
	</p>

	<?= Html::img('@web/img/help/bip-spaces-ui.png', ['alt' => 'BIP! Spaces UI', 'class' => 'img-responsive screenshot center-block', 'width' => 630, 'height' => 345]) ?>


	<a href="#bip-spaces-ai-summaries" class="no-underline"><h4 id="bip-spaces-ai-summaries">AI-generated summaries (for registered users)</h4></a>

	<p class='help-text'>
		Registered BIP! Space end-users have also the option to ask for AI-generated summaries of the top search results. This can be done by clicking on the “Summarise top results” button placed at the top right corner of the search results section. The summaries are generated by a generative AI model that synthesizes the titles and abstracts from the top-k search results. By default, k is set to 5, but users can adjust this value to regenerate the summary based on a different number of results. Please note that a daily quota limits the number of summaries each user can produce, and a counter displays the remaining allowance.    
	</p>
	<p class='help-text'>
		This feature offers an interesting use case: comparing summaries generated using different ranking schemes. For example, a substantial difference in the narrative between summaries based on <?= Html::a('popularity', Url::to(['site/indicators']) . '#Popularity', ['class' => 'main-green', 'target' => '_blank']) ?>  versus <?= Html::a('influence', Url::to(['site/indicators']) . '#Influence', ['class' => 'main-green', 'target' => '_blank']) ?> ranking could indicate a shift in the prevailing literature's focus over time.
	</p>

	<a href="#bip-spaces-feedback" class="no-underline"><h4 id="bip-spaces-feedback">Service feedback (for registered users, if enabled by the owner)</h4></a>

	<p class='help-text'>
	BIP! Space owners can enable the “Feedback Mode” to gather end-user input. This mode adds two distinct feedback mechanisms to the space:

	<ul class="help-text">
		<li>Result Evaluation: Users can assess the relevance or appropriateness of search results by clicking the “✓” (tick) or “x” (cross) icon next to an entry.</li>
		<li>Annotation Verification: Users confirm the correctness of an annotation by clicking “✓” or report an error by clicking “x”.</li>
	</ul>

	<p class='help-text'>
		All feedback (“✓” or “x”) is immediately stored in the internal database upon clicking. Crucially, this feedback does not immediately alter the underlying database or knowledge base. Changes based on the collected feedback can be implemented later, upon request from the Space owner. If a feedback button was clicked accidentally, the user can click the same button again to cancel the submission.
	</p>

	<a href="#bip-spaces-config" class="no-underline"><h4 id="bip-spaces-config">Service configuration (for space owners)</h4></a>
	<p class='help-text'>
		Each space can be tailored according to the needs of the organisation or community that owns it. There are various customisation options related to:
		<ul class="help-text">
			<li>The styling of the space (e.g., logo, color schemes).</li>
			<li>The default search configuration (e.g., default ranking scheme and filters).</li>
		</ul>
	</p>
	<p class='help-text'>
		Apart from that, the space owner can determine the knowledge base to be used for the annotations, as well as the set of annotations to be displayed in the results. Currently, all these configurations are made by the BIP! Services support team after consultation with the space owner. 
	</p>
</div>
