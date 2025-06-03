<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Request;
use yii\web\View;

$this->title = 'BIP! Data & API';
$this->registerJsFile('@web/js/third-party/swagger/swagger-ui-bundle.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/swagger/swagger-ui-standalone-preset.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/third-party/swagger/swagger-ui.css');
$this->registerCssFile('@web/css/swagger.css');

?>
    <div class="site-about help-text">

        <div class="data-section">

            <h1>
                Data
            </h1>
            <hr/>

            <!-- BIP! Overview -->
            <p>
                BIP! currently contains data for <b>more than <?= $articlesCount ?>M research works</b>. 
                For each work, the database includes metadata such as its DOI, title, author list, publication year, and venue, 
                as well as its citations. 
                These citations are used to construct a comprehensive citation network of the stored research works, 
                on top of which the provided (citation-based) <a href='<?= Url::to(['site/indicators']) ?>' class="main-green">impact indicators</a> are calculated.
            </p>

            <!-- Data Sources -->
            <div class="data-sources">
                <h3>
                    <i class="fa fa-circle-nodes"></i> Citation network
                </h3>
                <p>
                    The underlying citation network is constructed using data from the 
                    
                    <a href="https://graph.openaire.eu/" class="main-green" target="_blank">
                        OpenAIRE Graph
                        <i class="fa fa-external-link-square" aria-hidden="true"></i></a>, 
                        retaining only research works with a valid publication year and citations originating from the following sources:
                </p>
                
                <ul>
                    <li>
                        <a href="http://opencitations.net/" class="main-green" target="_blank">
                            OpenCitations' COCI dataset <i class="fa fa-external-link-square" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.microsoft.com/en-us/research/project/microsoft-academic-graph/" class="main-green" target="_blank">
                            Microsoft Academic Graph (MAG) <i class="fa fa-external-link-square" aria-hidden="true"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.crossref.org/services/metadata-delivery/rest-api/" class="main-green" target="_blank">
                            Crossref <i class="fa fa-external-link-square" aria-hidden="true"></i>
                        </a>
                    </li>
                </ul>
                <p>
                    Additionally, 
                    2nd level concepts from <a href="https://openalex.org/" class="main-green" target="_blank">OpenAlex <i class="fa fa-external-link-square" aria-hidden="true"></i></a> are used for topic classification.
                </p>
            </div>

            <!-- Dataset Dumps -->
            <div class="dataset-dumps">

                <a href="#downloads" class="no-underline">
                    <h3 id="downloads">
                        <i class="fa fa-download"></i> Downloads    
                    </h3>
                </a>

                <p>
                    The latest dataset dump including all citation-based impact indicators is available at 
                    <a href="https://doi.org/10.5281/zenodo.4386934" class="main-green" target="_blank">
                        10.5281/zenodo.4386934 <i class="fa fa-external-link-square" aria-hidden="true"></i>
                    </a>
                    <div class="grey-text">
                        &mdash; Intermediate releases (versions 3 to 4) can be found on
                        <a href="https://figshare.com/articles/dataset/BIP_DB_A_Dataset_of_Impact_Measures_for_Scientific_Publications/16733170/1" class="main-green" target="_blank">
                            figshare <i class="fa fa-external-link-square" aria-hidden="true"></i>
                        </a>
                    </div>
                </p>
                
                <p>
                    Note that, all impact indicators, starting from version 5.0.0, are also available via the 
                    <a href="https://zenodo.org/doi/10.5281/zenodo.3516917" class="main-green" target="_blank">
                        OpenAIRE Graph Dataset <i class="fa fa-external-link-square" aria-hidden="true"></i>
                    </a>
                </p>
            </div>

            <!-- BIP! NDR Dataset -->
            <div class="bip-ndr-section panel panel-default">
                <div class="panel-heading main-green">
                    <i class="fa fa-exclamation-circle"></i> BIP! NDR (No DOI Refs) dataset
                </div>
                <div class="panel-body">
                    The <b>BIP! NDR</b> dataset includes more than <b>3.6M citations</b> exracted from approximately 
                    <b>192K Computer Science conference or workshop papers</b> that, according to DBLP, lack DOIs. 
                    It is available at
                    <a href="https://doi.org/10.5281/zenodo.7962019" class="main-green" target="_blank">
                            10.5281/zenodo.7962019 <i class="fa fa-external-link-square" aria-hidden="true"></i>
                        </a>
                </div>
            </div>
        </div>

        <p>
            <a href="#api" class="no-underline" style="color: inherit;">
                <h2 id="api">
                    API
                </h2>
            </a>
            All impact indicators calculated by BIP! can be retrieved via the interface below or via our <a href="https://bip-api.imsi.athenarc.gr/documentation" class="main-green" target="_blank">public API <i class='fa fa-external-link-square' aria-hidden='true'></i></a>.
        </p>

        <div id="swagger-ui" class="panel panel-default"></div>

        <script>
            window.onload = function() {
              const ui = SwaggerUIBundle({
                url: "https://bip-api.imsi.athenarc.gr/swagger.json",
                dom_id: '#swagger-ui',
                deepLinking: true,

                presets: [
                  SwaggerUIBundle.presets.apis,
                  SwaggerUIStandalonePreset
                ]
              })

              window.ui = ui
            }
        </script>

        <p>
            <h2>Terms of use</h2>
            The datasets and API are provided on an "as-is" basis, without any guarantees or warranties of any kind; they are made available under the terms of the <a href="https://creativecommons.org/public-domain/cc0/" target="blank" class="main-green">Creative Commons Zero License (CC0)</a>.
        </p>

        <p>
            <h3>Code</h3>
            BIP! currently maintains the following code repositories:
            <ul>
                <li>
                    BIP! Ranker (<a href="https://github.com/athenarc/Bip-Ranker" class="main-green" target="_blank">repository</a>, <a href="https://doi.org/10.5281/zenodo.10564109" class="main-green" target="_blank">10.5281/zenodo.10564109<a>)
                </li>
                <li>
                    <a href="https://github.com/athenarc/bip-scholar-indicators" class="main-green" target="_blank">BIP! Scholar Indicators</a>
                </li>
                <li>
                    <a href="https://github.com/athenarc/bip-ndr-workflow" class="main-green" target="_blank">BIP! NDR workflow</a>
                </li>
            </ul>
        </p>
    </div>
