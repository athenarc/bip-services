<?php

/* @var $this yii\web\View */
use yii\helpers\Html;

$this->registerJsFile('@web/js/fixed-sidebar.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerCssFile('@web/css/fixed-sidebar.css');

$this->title = 'BIP! impact indicators';

?>

<div id="indicators" class="container">
    <div class="row">
        <div class="col-md-3 sidebar">
            <div id="toc-panel">
                <div class="sidebar-body">
                    <?php foreach ($indicators as $indicator_level => $indicator_level_array) { ?>
                        <h5 class="toc-heading">
                            <a href="#<?= str_replace(' ', '_', $indicator_level) ?>" class="green-bip"><?= $indicator_level ?></a>
                        </h5>
                            <?php foreach ($indicator_level_array as $indicator_semantics => $indicator_semantics_array) { ?>
                                <?php 
                                    $collapse_id = str_replace(' ', '_', $indicator_level) . '_' . str_replace(' ', '_', $indicator_semantics);
                                ?>
                                <div class="toc-link" data-toggle="collapse" href="#<?= $collapse_id ?>" aria-expanded="false" aria-controls="<?= $collapse_id ?>"><?= $indicator_semantics ?>
                                <span class="caret"></span>    
                                </div>
                                <ul class="collapse" id="<?= $collapse_id ?>">
                                    <?php foreach ($indicator_semantics_array as $indicator => $indicator_array) { ?>
                                        <?php $indicator_id = str_replace(' ', '_', $indicator); ?>
                                        <li class="toc-item"><a href="#<?= $indicator_id ?>" class="toc-link"><?= $indicator ?></a></li>
                                    <?php } ?>
                                </ul>
                            <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="col-md-9 main-content">
        <div class="help-text">
                <h1><?= Html::encode($this->title) ?></h1>
                <hr/>

                The vision behind the BIP! Services is the creation of an ecosystem of added-value services and resources, which are based on advanced, transparently calculated, explainable, and well-documented indicators that reflect a variety of aspects of the impact and merit of scientific works and researchers. They are built upon the data of popular scientific knowledge bases (mainly the <a href="https://graph.openaire.eu/" target="_blank">OpenAIRE Graph</a>) and they aim to facilitate tasks like scientific discovery and research assessment, adopting various suggestions made by relevant initiatives (e.g., <a href="https://sfdora.org/read/" target="_blank">DORA</a>, <a href="https://coara.eu/" target="_blank">CoARA</a>). This page summarizes all indicators which are available by various BIP! Services, along with explanations about their main intuition, the way they are calculated, and their most important limitations, in an attempt to educate the BIP! Services users and help them avoid common pitfalls and misuses.

                <?php foreach ($indicators as $indicator_category => $indicator_category_array) {?>

                    <h2 id='<?= str_replace(' ', '_', $indicator_category) ?>' class="green-bip"><strong><?= $indicator_category ?></strong></h2>

                    <?php foreach ($indicator_category_array as $indicator_family => $indicator_family_array) {?>            
                        <div class="indicator-section" id="<?= str_replace(' ', '_', $indicator_family) ?>">
                            <h3 id="<?= str_replace(' ', '_', $indicator_family) ?>" class="indicator-heading"><?= $indicator_family ?></h3>

                            <?php foreach ($indicator_family_array as $indicator => $indicator_array) { ?>
                                <?php $indicator_id = str_replace(' ', '_', $indicator) ?>
                                <div class="card">
                                    <div class="card-body">
                                        <h4 id="<?= str_replace(' ', '_', $indicator) ?>" class="header-scroll"><?= $indicator ?></h4>
                                        <?php if (!empty($indicator_array[''])): ?>
                                            <p class="indicator-label">Description</p>
                                            <p><?= $indicator_array[''] ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($indicator_array['Intuition'])): ?>
                                            <p class="indicator-label">Intuition</p>
                                            <p><?= $indicator_array['Intuition'] ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($indicator_array['Data & calculation'])): ?>
                                            <p class="indicator-label">Data & calculation</p>
                                            <p><?= $indicator_array['Data & calculation'] ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($indicator_array['Parameters'])): ?>
                                            <p class="indicator-label">Parameters</p>
                                            <p><?= $indicator_array['Parameters'] ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($indicator_array['Limitations'])): ?>
                                            <p class="indicator-label">Limitations</p>
                                            <p><?= $indicator_array['Limitations'] ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($indicator_array['Availability'])): ?>
                                            <p><span class="indicator-label">Availability:</span> <?= $indicator_array['Availability'] ?></p>
                                        <?php endif; ?>

                                        <?php if (!empty($indicator_array['Code'])): ?>
                                            <p><span class="indicator-label">Code:</span> <a target='_blank' class='green-bip' href='<?= $indicator_array['Code'] ?>'> <?= $indicator_array['Code'] ?></a></p>
                                        <?php endif; ?>

                                        <?php if (!empty($indicator_array['References'])): ?>
                                            <p class="indicator-label">References</p>
                                            <?= $indicator_array['References'] ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

