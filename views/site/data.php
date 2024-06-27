<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Request;
use yii\web\View;

$this->title = 'BIP! Services - Data & API';
$this->registerJsFile('@web/js/third-party/swagger/swagger-ui-bundle.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/third-party/swagger/swagger-ui-standalone-preset.js', ['position' => View::POS_HEAD, 'depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerCssFile('@web/css/third-party/swagger/swagger-ui.css');
$this->registerCssFile('@web/css/swagger.css');

?>
    <div class="site-about help-text">
        <!-- <h1><?= Html::encode($this->title) ?></h1> -->

        <h3>Data</h3>
        <p>
        BIP! Finder currently contains data for <b>more than  <?= $articlesCount ?>M research works</b>. For each article, BIP! Finder's database contains some metadata (e.g. its DOI, author list, publication year and venue, etc.) along with its citations. These citations are used to construct the citation network of the stored scientific articles.
        The data used to produce the citation network on which we calculate the provided measures are gathered from the <a href= "https://graph.openaire.eu/" class='main-green' target="_blank"> OpenAIRE Graph <i class='fa fa-external-link-square' aria-hidden='true'></i></a> and keeping only citations coming from <a href="http://opencitations.net/" class='main-green' target="_blank">OpenCitations' COCI dataset <i class='fa fa-external-link-square' aria-hidden='true'></i></a>, <a href="https://www.microsoft.com/en-us/research/project/microsoft-academic-graph/" class='main-green' target="_blank">MAG <i class='fa fa-external-link-square' aria-hidden='true'></i></a>, and <a href="https://www.crossref.org/services/metadata-delivery/rest-api/" class='main-green' target="_blank">Crossref <i class='fa fa-external-link-square' aria-hidden='true'></i></a>.
        It should be noted that, we use 2nd level concepts from <a href="https://openalex.org/" class='main-green' target="_blank">OpenAlex <i class='fa fa-external-link-square' aria-hidden='true'></i></a> for our topics. Last but not least, since some of the indicators require for their calculation the year of the publication to be present, we keep in our database only those DOIs for which we can gather this minimum piece of information from at least one data source.
        </p>
        <p>
        The current dataset dump containing the latest computed impact aspect scores can be found on <a href="https://doi.org/10.5281/zenodo.4386934" class='main-green' target="_blank">10.5281/zenodo.4386934 <i class='fa fa-external-link-square' aria-hidden='true'></i></a>.
        Note that an intermediate release (between Version 3 and 4) can be found on <a href="https://figshare.com/articles/dataset/BIP_DB_A_Dataset_of_Impact_Measures_for_Scientific_Publications/16733170/1" class='main-green' target="_blank">figshare <i class='fa fa-external-link-square' aria-hidden='true'></i></a>.
        Furthermore, it's important to mention that the impact scores computed by BIP! are now accessible through the <a href="https://zenodo.org/doi/10.5281/zenodo.3516917" class='main-green' target="_blank">OpenAIRE Graph Dataset <i class='fa fa-external-link-square' aria-hidden='true'></i></a> starting from version 5.0.0 onwards.
        </p>
        <p>
            The BIP! NDR (No DOI Refs) dataset is also available at <a href="https://doi.org/10.5281/zenodo.7962019" class='main-green' target="_blank">10.5281/zenodo.7962019 <i class='fa fa-external-link-square' aria-hidden='true'></i></a>. It contains more than 2.9M citations made by approximately 171K Computer Science conference or workshop papers that, according to DBLP, do not have a DOI.
        </p>

        <p>
            <em>
                <b class="main-green">Terms of use:</b> These data are provided "as is", without any warranties of any kind. The data are provided under the Creative Commons Attribution 4.0 International license.
            </em>
        </p>

        <p>
        <h3>API</h3>
        All impact aspect scores measured by us can be retrieved via the interface below or via our <a href="https://bip-api.imsi.athenarc.gr/documentation" class="main-green" target="_blank">public API <i class='fa fa-external-link-square' aria-hidden='true'></i></a>.
        </p>

        <div id="swagger-ui"></div>

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
        <br/>
        <p>
            <em>
                <b class="main-green">Terms of use:</b> These data are provided "as is", without any warranties of any kind. The data are provided under the Creative Commons Attribution 4.0 International license.
            </em>
        </p>

        <p>
            <h3>Code</h3>
            BIP! Software currently utilises the following libraries to compute impact indicators:
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
