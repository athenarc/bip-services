<?php

use app\models\ScholarSearchForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'BIP! Services - Scholar';
$this->registerCssFile('@web/css/scholar-landing.css');

$scholar_search_model = new ScholarSearchForm();

?>

<div id="scholar-landing">
    <h1 class="text-center">BIP! Scholar</h1>
    <div class="scholar-landing-banner">
        <?= Html::img('@web/img/scholar/bip-scholar.jpg', [
            'class' => 'img-responsive scholar-landing-banner__img',
            'alt' => 'BIP! Scholar',
            'width' => 1536,
            'height' => 1024,
        ]) ?>
        <div class="scholar-landing-banner__actions" role="toolbar" aria-label="Scholar quick actions">
            <div class="row scholar-landing-banner__actions-row">
                <div class="col-xs-12 col-md-offset-3 col-md-6 scholar-landing-banner__search-col">
                    <?php
                    $form = ActiveForm::begin([
                        'id' => 'search-form',
                        'method' => 'GET',
                        'action' => Url::to(['scholar/search']),
                        'options' => ['class' => 'scholar-landing-banner__search-form', 'role' => 'search'],
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => false,
                    ]);
                    ?>
                    <div class="has-search">
                        <?= $form->field($scholar_search_model, 'keywords', [
                            'template' => "{input}<span class='glyphicon glyphicon-search form-control-feedback'></span>",
                            'enableClientValidation' => false,
                        ])
                            ->input('search', [
                                'aria-label' => 'Search',
                                'placeholder' => 'Search existing open profiles',
                                'class' => 'search-box form-control',
                            ]) ?>
                        <input type="submit" class="sr-only" hidefocus="true" tabindex="-1">
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
                <div class="col-xs-12 col-md-3 text-right scholar-landing-banner__profile-col">
                    <a href="<?= Url::to(['scholar/profile']) ?>" class="btn btn-default scholar-landing-banner__btn scholar-landing-banner__profile-btn">
                        <?php
                        if (Yii::$app->user->isGuest) {
                            echo 'Sign In';
                        } elseif (! isset($researcher->orcid)) {
                            echo 'Create Profile';
                        } else {
                            echo 'My profile';
                        }
                        ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <ul class="list-unstyled scholar-landing-tab-buttons" role="tablist">
        <li class="active" role="presentation">
            <a
                href="#scholar-tab-researchers"
                class="btn btn-lg btn-default btn-block"
                aria-controls="scholar-tab-researchers"
                role="tab"
                data-toggle="tab"
            >
                For researchers
            </a>
        </li>
        <li role="presentation">
            <a
                href="#scholar-tab-creators"
                class="btn btn-lg btn-default btn-block"
                aria-controls="scholar-tab-creators"
                role="tab"
                data-toggle="tab"
            >
                For profile template creators
            </a>
        </li>
    </ul>

    <div class="tab-content scholar-landing-tab-panels">
        <div id="scholar-tab-researchers" class="tab-pane fade in active" role="tabpanel">
            <h2 class="scholar-landing-title help-text">
                Make every contribution count. <span class="scholar-landing-title__line2">Shape your research story for different contexts.</span>
            </h2>
            <div class="card">
                <div class="card-body">
                    <p class="card-text help-text">
                        BIP! Scholar offers researchers the option to create public or private profiles
                        representing their research activities, enrich them with contextual information
                        (e.g., CRediT roles, indicators, narratives), and highlight different aspects of
                        their careers, enriched with valuable additional context.
                    </p>
                    <p class="card-text help-text">
                        A key objective of the platform is to offer a variety of profile templates that
                        cover a wide range of research activities, going beyond scientific publications.
                        The platform supports not only traditional track-record-based profiles
                        (<a href="<?= Html::encode(Url::to(['scholar/profile', 'orcid' => '0000-0003-0555-4128', 'template_url_name' => 'Inclusive_Profile'])) ?>" class="main-green">example</a>)
                        but also narrative-style or hybrid CVs
                        (<a href="<?= Html::encode(Url::to(['scholar/profile', 'orcid' => '0000-0003-0555-4128', 'template_url_name' => 'Résumé_for_Researchers_(Royal_Society)'])) ?>" class="main-green">example</a>),
                        which can help researchers present their careers in a more responsible and inclusive manner.
                    </p>
                    <p class="card-text help-text">
                        Finally, many BIP! Scholar profile templates can be dynamically tailored by the viewer
                        to facilitate the examination of a researcher's career according to specific topics,
                        roles, or types of activity that may be of particular interest.
                    </p>
                </div>
            </div>
        </div>
        <div id="scholar-tab-creators" class="tab-pane fade" role="tabpanel">
            <h2 class="scholar-landing-title help-text">
                Create. Pilot. Improve. <span class="scholar-landing-title__line2">Repeat.</span>
            </h2>
            <div class="card">
                <div class="card-body">
                    <p class="card-text help-text">
                        Are you a research assessment expert exploring new ways to assess and represent
                        research contributions? Create your own profile templates, share them with volunteer
                        researchers, and gather real-world feedback to refine your ideas.
                    </p>
                    <p class="card-text help-text">
                        Interested? Get in touch with us at
                        <a href="mailto:bip@athenarc.gr" class="main-green">bip@athenarc.gr</a>
                        to request access.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
