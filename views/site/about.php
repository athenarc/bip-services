<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Request;
use yii\web\View;

$this->title = 'BIP! Services - About';
$this->registerJsFile('@web/js/toggleCollapseArrow.js', ['position' => View::POS_END, 'depends' => [\yii\web\JqueryAsset::className()]]);

?>

<div class="container">
    <h1>
        About
    </h1>
    <hr/>

    <h3>BIP! Services</h3>
    <div class="help-text">
        <p>
            <b>The suite</b>. BIP! Services, is a suite of services designed to support researchers and other stakeholders with scientific knowledge discovery, research assessment, and other use cases related to their everyday routines. The suite comprises four services, <a href='<?= Url::to(['/search']) ?>' class="main-green" target="_blank">Finder</a>, <a href='<?= Url::to(['/scholar']) ?>' class="main-green" target="_blank">Scholar</a>, <a href='<?= Url::to(['/readings']) ?>' class="main-green" target="_blank">Readings</a>, and <a href='<?= Url::to(['/spaces']) ?>' class="main-green" target="_blank">Spaces</a>, each offering unique functionalities addressed to all professionals conducting research. professionals, and technologists across all disciplines, fostering interdisciplinary cooperation. For more information regarding BIP! Services visit <a href='<?= Url::to(['/site/home']) ?>' class="main-green" target="_blank">here</a>.
        </p>
        <p>
            <b>History</b>. The first service offered was BIP! (<u>BI</u>bliogra<u>P</u>hy) Finder in 2016, which also inspired the name of the suite, followed by Readings, Scholar, and Spaces. BIP! Finder was originally built on top of <a href='https://www.nlm.nih.gov/medline/medline_home.html' class="main-green" target="_blank"> MEDLINE</a>, focusing on the exploration of publications in the Life Sciences domain. This focus was reflected in the original meaning of the BIP! acronym: Biomedical Publications Finder. However, starting in 2018, the service began incorporating multidisciplinary data, prompting a rebranding to better represent this broader scope. The <a href='https://dl.acm.org/doi/10.1145/3357384.3357850' class="main-green" target="_blank">first peer-reviewed article presenting BIP! Finder</a> was published in the Proceedings of the 28th ACM International Conference on Information and Knowledge Management (CIKM 2019) and was presented between November 3rd-7th in Beijing, where the conference was held.
        </p>
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


    <h3>
        <a href="#former-team" data-toggle="collapse" class="custom-collapse text-decoration-none" 
        aria-expanded="false">
            <span class="green-text">Former team members</span>
            <i id="custom_expand_icon" class="fa fa-chevron-down grey-link" style="font-size:0.8em;"></i>
        </a>
    </h3>

    <div id="former-team" class="collapse help-text-left" role="tabpanel">
        <div class="row">
            <?php foreach (Yii::$app->params['teamMembers']['former'] as $member): ?>
                <div class="col-md-4 col-xs-12">
                    <div class="flex-wrap items-center">
                        <?= Html::img($member['imgUrl'], ['alt' => $member['name'], 'class' => 'img-circle team-member-img']) ?>
                        <div>
                            <a href="<?= $member['link'] ?>" class="main-green" target="_blank">
                                <?= $member['name'] ?> 
                                <i class="fa fa-external-link-square" aria-hidden="true"></i>
                            </a>
                            <div><?= $member['email'] ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <h3>How to cite</h3>

    <div class="help-text">

        <p>We kindly ask that any published research using <b>BIP! Finder</b>, <b>BIP! Scholar</b>, <b>BIP! DB</b>, or <b>BIP! NDR</b> cites the corresponding papers listed below:</p>

        <div class="citation-section">
            <h4>BIP! Finder</h4>
            <div class="panel panel-default">
                <div class="panel-body">
                    T. Vergoulis, S. Chatzopoulos, I. Kanellos, P. Deligiannis, C. Tryfonopoulos, T. Dalamagas: 
                    <b>BIP! Finder: Facilitating scientific literature search by exploiting impact-based ranking.</b> 
                    <i>Proceedings of the 28<sup>th</sup> ACM International Conference on Information and Knowledge Management (CIKM)</i>, 2019 
                    <small><?= Html::a("(BibTeX)", "@web/files/bip-finder.bib", ['class' => 'grey-link']) ?></small>
                </div>
            </div>

            <h4>BIP! Scholar</h4>
            <div class="panel panel-default">
                <div class="panel-body">
                    T. Vergoulis, S. Chatzopoulos, K. Vichos, I. Kanellos, A. Mannocci, N. Manola, P. Manghi: 
                    <b>BIP! Scholar: A Service to Facilitate Fair Researcher Assessment.</b> 
                    <i>Joint Conference on Digital Libraries (JCDL)</i>, 2022 
                    <small><?= Html::a("(BibTeX)", "@web/files/bip-scholar.bib", ['class' => 'grey-link']) ?></small>
                </div>
            </div>

            <h4>BIP! DB</h4>
            <div class="panel panel-default">
                <div class="panel-body">
                    T. Vergoulis, I. Kanellos, C. Atzori, A. Mannocci, S. Chatzopoulos, S. La Bruzzo, N. Manola, P. Manghi: 
                    <b>BIP! DB: A Dataset of Impact Measures for Scientific Publications.</b> 
                    <i>International Workshop on Scientific Knowledge: Representation, Discovery, and Assessment (Sci-K) @ The Web Conf</i>, 2021 
                    <small><?= Html::a("(BibTeX)", "@web/files/bip-db.bib", ['class' => 'grey-link']) ?></small>
                </div>
            </div>

            <h4>BIP! NDR</h4>
            <div class="panel panel-default">
                <div class="panel-body">
                    P. Koloveas, S. Chatzopoulos, C. Tryfonopoulos, T. Vergoulis: 
                    <b>BIP! NDR (NoDoiRefs): A Dataset of Citations From Papers Without DOIs in Computer Science Conferences and Workshops.</b> 
                    <i>International Conference on Theory and Practice of Digital Libraries (TPDL)</i>, 2023 
                    <small><?= Html::a("(BibTeX)", "@web/files/bip-ndr.bib", ['class' => 'grey-link']) ?></small>
                </div>
            </div>
        </div>

        <p>Thank you for supporting these tools and datasets by acknowledging their respective publications.</p>

    </div>


    <h3>        
        <a href="#other-relevant-publications" data-toggle="collapse" class="custom-collapse text-decoration-none" 
        aria-expanded="false">
            <span class="green-text">Other relevant publications</span>
            <i id="custom_expand_icon" class="fa fa-chevron-down grey-link" style="font-size:0.8em;"></i>
        </a>
    </h3>

    <div id="other-relevant-publications" class="collapse help-text" role="tabpanel">

        <p>Please also consider citing the following papers:</p>

        <div class="panel panel-default">

            <div class="panel-body">
            
                <p>I. Kanellos, T. Vergoulis, D. Sacharidis, T. Dalamagas, Y. Vassiliou: <b>Impact-Based Ranking of Scientific Publications: A Survey and Experimental Evaluation.</b> <i>IEEE Transactions on Knowledge and Data Engineering (TKDE)</i>, 2019 <small><?= Html::a("(BibTeX)", "@web/files/survey.bib", [ 'class' => 'grey-link' ]) ?></small></p>

                <p>I. Kanellos, T. Vergoulis, D. Sacharidis, T. Dalamagas, Y. Vassiliou: <b>Ranking papers by their short-term scientific impact.</b> <i>International Conference on Data Engineering (ICDE)</i>, 2021 <small><?= Html::a("(BibTeX)", "@web/files/attrank.bib", [ 'class' => 'grey-link' ]) ?></small></p>

                <p>S. Chatzopoulos, T. Vergoulis, I. Kanellos, T. Dalamagas, C. Tryfonopoulos: <b>ArtSim: Improved estimation of current impact for recent articles.</b> <i>International Workshop on Assessing Impact and Merit in Science (AimInScience) @ TPDL</i>, 2020 <small><?= Html::a("(BibTeX)", "@web/files/artsim.bib", [ 'class' => 'grey-link' ]) ?></small></p>

                <p>S. Chatzopoulos, T. Vergoulis, I. Kanellos, T. Dalamagas, C. Tryfonopoulos: <b>Further Improvements on Estimating the Popularity of Recently Published Papers.</b> <i>Quantitative Science Studies (QSS)</i>, 2021 <small><?= Html::a("(BibTeX)", "@web/files/artsimplus.bib", [ 'class' => 'grey-link' ]) ?></small></p>

                <p>S. Chatzopoulos, K. Vichos, I. Kanellos, T. Vergoulis: <b>Piloting topic-aware research impact assessment features in BIP! Services.</b> <i>Extended Semantic Web Conference (ESWC)</i>, 2023 <small><?= Html::a("(BibTeX)", "@web/files/topic-aware.bib", [ 'class' => 'grey-link' ]) ?></small></p>
            </div>
        </div>
        
    </div>

    

    <h3>Contact us</h3>
    <div class="help-text-left">
        <div>Send us your feedback at:</div>
        <div style="margin-left:20px">
            <div><i class="fa fa-fw fa-envelope" aria-hidden="true"></i> <b>Email:</b> <a href="mailto:bip@athenarc.gr" class="main-green">bip@athenarc.gr</a></div>
            <div><i class="fa fa-fw fa-map-marker" aria-hidden="true"></i> <b>Address:</b> Athena Research Center, Egialias 19 & Chalepa, Maroussi 15125, Greece</div>
        </div>
    </div>
</div>
