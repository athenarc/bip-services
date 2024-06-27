<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Request;

$this->title = 'BIP! Services - About';

?>

<div class="container">


    <h3>How to cite</h3>
    <div class="help-text">
            If you are using BIP! Finder please cite:
            </br></br>
            <div class="panel panel-default">
                    <div class="panel-body">
                            T. Vergoulis, S. Chatzopoulos, I. Kanellos, P. Deligiannis, C. Tryfonopoulos, T. Dalamagas: <b>BIP! Finder: Facilitating scientific literature search by exploiting impact-based ranking.</b> <i>In Proceedings of the 28<sup>th</sup> ACM International Conference on Information and Knowledge Management (CIKM)</i>, Beijing, China, November 2019 <small><?= Html::a("(BibTeX)", "@web/files/bip.bib") ?></small>
                    </div>
            </div>

            If you are using BIP! DB please cite:
            </br></br>
            <div class="panel panel-default">
                    <div class="panel-body">
                        T. Vergoulis, I. Kanellos, C. Atzori, A. Mannocci, S. Chatzopoulos, S. La Bruzzo, N. Manola, P. Manghi: <b>BIP! DB: A Dataset of Impact Measures for Scientific Publications.</b> <i>Companion Proceedings of the Web Conference</i> 2021 <small><?= Html::a("(BibTeX)", "@web/files/bipdb.bib") ?></small>
                    </div>
            </div>

            If you are using BIP! Scholar please cite:
            </br></br>
            <div class="panel panel-default">
                    <div class="panel-body">
                    T. Vergoulis, S. Chatzopoulos, K. Vichos, I. Kanellos, A. Mannocci, N. Manola, P. Manghi: <b>BIP! Scholar: A Service to Facilitate Fair Researcher Assessment.</b> <i>Joint Conference on Digital Libraries (JCDL)</i> 2022
                    </div>
            </div>

            If you are using BIP! NDR please cite:
            </br></br>
            <div class="panel panel-default">
                    <div class="panel-body">
                    P. Koloveas, S. Chatzopoulos, C. Tryfonopoulos, T. Vergoulis: <b>BIP! NDR (NoDoiRefs): A Dataset of Citations From Papers Without DOIs in Computer Science Conferences and Workshops.</b> <i>International Conference on Theory and Practice of Digital Libraries (TPDL)</i> 2023
                    </div>
            </div>

        We kindly ask that any published research that makes use of BIP! Finder, DB or Scholar cites the corresponding paper(s) above.
    </div>

    <h3>Other relevant publications</h3>
    <div class="help-text">
        Please also consider citing the following papers:
        <br/><br/>
        <div class="panel panel-default">
            <div class="panel-body">
                    I. Kanellos, T. Vergoulis, D. Sacharidis, T. Dalamagas, Y. Vassiliou: <b>Impact-Based Ranking of Scientific Publications: A Survey and Experimental Evaluation.</b> <i>IEEE Transactions on Knowledge and Data Engineering (TKDE)</i> 2019 <small><?= Html::a("(BibTeX)", "@web/files/survey.bib") ?></small>
                    <br/><br/>

                    I. Kanellos, T. Vergoulis, D. Sacharidis, T. Dalamagas, Y. Vassiliou: <b>Ranking papers by their short-term scientific impact.</b> <i>International Conference on Data Engineering (ICDE)</i> 2021 <small><?= Html::a("(BibTeX)", "@web/files/attrank.bib") ?></small>

                    <br/><br/>
                    S. Chatzopoulos, T. Vergoulis, I. Kanellos, T. Dalamagas, C. Tryfonopoulos: <b>ArtSim: Improved estimation of current impact for recent articles.</b> <i>AimInScience @ TPDL</i> 2020 <small><?= Html::a("(BibTeX)", "@web/files/artsim.bib") ?></small>
                    <br/><br/>

                    S. Chatzopoulos, T. Vergoulis, I. Kanellos, T. Dalamagas, C. Tryfonopoulos: <b>Further Improvements on Estimating the Popularity of Recently Published Papers.</b> <i>Quantitative Science Studies (QSS)</i> 2021 <small><?= Html::a("(BibTeX)", "@web/files/artsimplus.bib") ?></small>
                    <br/><br/>

                    S. Chatzopoulos, K. Vichos, I. Kanellos, T. Vergoulis: <b>Piloting topic-aware research impact assessment features in BIP! Services.</b> <i>Extended Semantic Web Conference (ESWC)</i> 2023 
                    <!-- <small><?= Html::a("(BibTeX)", "@web/files/artsimplus.bib") ?></small> -->
                </div>
        </div>
    </div>

    <h3>Our team</h3>
    <div class="help-text-left">
        <div class="row">
            <div class="flex-wrap items-center">
                <?php foreach(Yii::$app->params['teamMembers']['current']  as $member): ?>
                    <div class="col-md-4 col-xs-12">
                            <div class="flex-wrap items-center">
                            <?= Html::img($member['imgUrl'], ['alt' => $member['name'], 'class' => 'img-circle team-member-img']) ?>
                            <div>
                                <a href="<?= $member['link'] ?>" class="main-green" target="_blank"><?= $member['name'] ?> <i class="fa fa-external-link-square" aria-hidden="true"></i></a>
                                <div><?= $member['email'] ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <h3>Former team members</h3>
    <div class="help-text-left">
        <div class="row">
            <div class="flex-wrap items-center">
                <?php foreach(Yii::$app->params['teamMembers']['former']  as $member): ?>
                    <div class="col-md-4 col-xs-12">
                            <div class="flex-wrap items-center">
                            <?= Html::img($member['imgUrl'], ['alt' => $member['name'], 'class' => 'img-circle team-member-img']) ?>
                            <div>
                                <a href="<?= $member['link'] ?>" class="main-green" target="_blank"><?= $member['name'] ?> <i class="fa fa-external-link-square" aria-hidden="true"></i></a>
                                <div><?= $member['email'] ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <h3>Contact us</h3>
    <div class="help-text-left">
        <div>Send us your feedback at:</div>
        <div style="margin-left:20px">
            <div><i class="fa fa-fw fa-envelope" aria-hidden="true"></i> <b>Email:</b> bip@athenarc.gr</div>
            <div><i class="fa fa-fw fa-map-marker" aria-hidden="true"></i> <b>Address:</b> Athena RC, Artemidos 6 & Epidavrou, Maroussi 15125, Greece</div>
        </div>
    </div>
</div>
