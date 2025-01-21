<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'BIP! Services - Settings';
$this->registerJsFile('@web/js/profile_visibility.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/settings.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/on-off-my-switch.css');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 col-xs-12">
            <div class="help-text-left">
                <h2>Settings</h2>
                <hr>
                <h3>Finder</h3>
                <div class="list-group list-group-shadow">
                    <div class="list-group-item">
                        <div class="flex-wrap items-center justify-between">
                            <div>
                                <span class="grey-text"><b>Keyword relevance</b></span>
                                <div class="text-muted-settings">Choose whether to consider keyword relevance when ranking results.</div>
                            </div>
                            <div class="my-switch">
                                <input type="checkbox" id="keyword-relevance-toggle" class="my-switch-input" <?= ($user->keyword_relevance) ? "checked" : "" ?> >
                                <label for="keyword-relevance-toggle" class="my-switch-slider"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <h3>Scholar</h3>
                    <div class="list-group list-group-shadow">
                        <?php if ($has_profile == false): ?>
                                <div class="list-group-item">
                                    <div>Your BIP! Scholar profile is not currently linked to ORCiD.</div>
                                    <div>Please, navigate to <a class='green-bip' href='<?= Url::to(['scholar/index']) ?>'>BIP! Scholar</a>, link your profile with your ORCiD, and start using our service.</div>
                                </div>
                        <?php else: ?>
                            <div class="list-group-item">
                                <div class="flex-wrap items-center justify-between">
                                    <div>
                                        <span class="grey-text"><b>Profile visibility</b></span>
                                        <span id = "profile-visibility-text" class="badge badge-outline badge-success"><?= ($user->researcher->is_public) ? "Public" : "Private" ?></span>
                                        <div class="text-muted-settings">Toggle the visibility of your Scholar profile.</div>
                                    </div>
                                    <div class="my-switch">
                                        <input type="checkbox" id="settings-public-switch" class="my-switch-input" <?= ($user->researcher->is_public) ? "checked" : "" ?> >
                                        <label for="settings-public-switch" class="my-switch-slider"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item">
                                <div class="flex-wrap items-center justify-between">
                                    <div>
                                    <div class="grey-text"><b>Unlink your profile</b></div>
                                        <div class="text-muted-settings">Unlink your Scholar profile from ORCiD.</div>
                                    </div>
                                    <?= Html::a('<i class="fa fa-unlink" aria-hidden="true"></i>', ['site/settings', 'unlink_profile' => true], ['id' => 'settings-unlink-switch', 'class'=>'btn btn-sm btn-default']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <h3>Privacy</h3>
                <div class="list-group list-group-shadow">
                    <div class="list-group-item">
                        <div class="flex-wrap items-center justify-between">
                            <div>
                            <div class="grey-text"><b>Manage your privacy settings</b></div>
                                <div class="text-muted-settings">Opt in or out from Matomo and/or Google Analytics.</div>
                            </div>
                            <?= Html::a('<i class="fa fa-lg fa-external-link-square"></i>', ['site/privacy-settings'], ['class'=>'main-green']) ?>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="flex-wrap items-center justify-between">
                            <div>
                            <div class="grey-text"><b>Review our Privacy and Cookie Policy</b></div>
                                <div class="text-muted-settings">Review our policy for the information that we collect.</div>
                            </div>
                            <?= Html::a('<i class="fa fa-lg fa-external-link-square"></i>', ['site/data-policy'], ['class'=>'main-green']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
