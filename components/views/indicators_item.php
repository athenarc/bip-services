<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;


?>

<div class="row">
    <div class="col-xs-12 text-center" >
        <div class="well profile" style="padding-top: 25px;">
            <div class="row">
                <div class="col-md-9 col-xs-12">
                    <div id="impact-indicators" class="inner-well well indicators-panel indicators-container-1200-height">
                        <span class="legend">Impact Indicators</span>

                        <div class="col-md-2 col-xs-12 indicator-div" data-value="Popular Works" id="popular-works">
                            <?php if(isset($element_config['Popular Works'])): ?>
                                <span class="indicator animate-indicator" data-target="<?= $popular_works_count ?>"><?= ($popular_works_count === null) ? "-" : 0 ?></span>
                                <p><small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Popular Works</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Impact']['Popular Works'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Popular_Works']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> <i class="fa fa-fire" aria-hidden="true"></i> popular works </span></small></p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small><i class="fa fa-fire" aria-hidden="true"></i> popular works </span></small></p>
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="col-md-2 col-xs-12 indicator-div" data-value="Influential Works" id="influential-works">
                            <?php if(isset($element_config['Influential Works'])): ?>
                                <span class="indicator animate-indicator" data-target="<?= $influential_works_count ?>"><?= ($influential_works_count === null) ? "-" : 0 ?></span>
                                <p><small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Influential Works</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Impact']['Influential Works'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Influential_Works']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> <i class="fa fa-university" aria-hidden="true"></i> influential works </span></small></p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small><i class="fa fa-university" aria-hidden="true"></i> influential works </span></small></p>
                                </span>
                            <?php endif; ?>

                        </div>

                        <div class="col-md-2 col-xs-12 indicator-div" data-value="Citations" id="citations">
                            <?php if(isset($element_config['Citations'])): ?>
                                <?php $citations_value = ($works_num == 0) ? "-" : $citations ?>
                                <span class="indicator animate-indicator" data-target="<?= $citations_value ?>"><?= ($citations_value === '-') ? '-' : 0 ?></span>
                                <p><small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Citations</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= htmlspecialchars($indicators['Impact']['Citations'], ENT_QUOTES) ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Citations']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> <i class="fa-solid fa-quote-left" aria-hidden="true"></i> citations </span></small></p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small><i class="fa-solid fa-quote-left" aria-hidden="true"></i> citations </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-2 col-xs-12 indicator-div" data-value="Aggregated Impulse" id="aggregated-impulse">
                            <?php if(isset($element_config['Aggregated Impulse'])): ?>
                                <?php $impulse_value = ($works_num == 0) ? "-" : $impulse ?>
                                <span class="indicator animate-indicator" data-target="<?= $impulse_value ?>"><?= ($impulse_value === '-') ? '-' : 0 ?></span>
                                <p><small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Impulse</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Impact']['Aggregated Impulse'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Aggregated_Impulse']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> <i class="fa fa-rocket" aria-hidden="true"></i> impulse </span></small></p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small><i class="fa fa-rocket" aria-hidden="true"></i> impulse </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-2 col-xs-12 indicator-div" data-value="h-index" id="h-index">
                            <?php if(isset($element_config['h-index'])): ?>
                                <?php $h_index_value = ($works_num == 0) ? "-" : $h_index ?>
                                <span class="indicator animate-indicator" data-target="<?= $h_index_value ?>"><?= ($h_index_value === '-') ? '-' : 0 ?></span>
                                <p><small><span role="button" data-toggle="popover" data-placement="auto" title="<b>H-index</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Impact']['h-index'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'H-index']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> h-index </span></small></p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small> h-index </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-2 col-xs-12 indicator-div" data-value="i10-index" id="i10-index">
                            <?php if(isset($element_config['i10-index'])): ?>
                                <?php $i10_index_value = ($works_num == 0) ? "-" : $i10_index ?>
                                <span class="indicator animate-indicator" data-target="<?= $i10_index_value ?>"><?= ($i10_index_value === '-') ? '-' : 0 ?></span>
                                <p><small><span role="button" data-toggle="popover" data-placement="auto" title="<b>i10-index</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Impact']['i10-index'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'i10-index']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> i10-index </span></small></p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small> i10-index </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div id="career-stage"class="inner-well well indicators-panel indicators-container-1200-height">
                        <span class="legend">Career Stage Indicators</span>

                        <div class="col-md-6 indicator-div" data-value="Academic Age" id="academic-age">
                            <?php if(isset($element_config['Academic Age'])): ?>
                                <?php $academic_age_value = (!empty($academic_age) || $academic_age === 0) ? $academic_age : "-"?>
                                <span id ="academic-age-indicator" class="indicator animate-indicator" data-target="<?= $academic_age_value ?>" data-academic-age = "<?= $academic_age ?>" data-min-year = "<?= $paper_min_year ?>"><?= ($academic_age_value === "-") ? "-" : 0 ?></span>
                                    <p><small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Academic Age</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Career Stage']['Academic Age'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Academic_Age']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> academic age </span></small></p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small> academic age </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 indicator-div" style="top: -5px;" data-value="Fair Academic Age" id="fair-academic-age">
                            <?php if(isset($element_config['Fair Academic Age'])): ?>
                                <?php $responsible_academic_age_value = (isset($responsible_academic_age) && (!empty($academic_age) || $academic_age === 0)) ? $responsible_academic_age : "-"?>
                                <span id ="responsible-academic-age" class="indicator animate-indicator" data-target="<?= $responsible_academic_age_value ?>"><?= ($responsible_academic_age_value === "-") ? "-" : 0 ?></span>
                                <p>
                                    <small>
                                        <span role="button" data-toggle="popover" data-placement="auto" title="<b>Fair Academic Age</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Career Stage']['Fair Academic Age'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Fair_Academic_Age']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>">fair academic age</span>
                                    </small>
                                    <span role ="button" data-toggle="modal" data-target="#academic-age-datepicker-modal"><i class="fa-solid <?= ($edit_perm) ? 'fa-pen-to-square' : 'fa-eye' ?> fa-xs"></i></span>
                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small> fair academic age </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div id="productivity-indicators" class="inner-well well indicators-panel indicators-container-1200-height">
                        <span class="legend">Productivity Indicators</span>

                        <div class="col-md-3 col-xs-12 indicator-div" data-value="Number of Publications" id="number-of-publications">
                            <?php if(isset($element_config['Number of Publications'])): ?>
                                <span class="indicator animate-indicator" data-target="<?= $papers_num ?>">0</span>
                                <p
                                <?php if ($missing_papers_num > 0 && $facets_selected == false): ?> style="margin: 0" <?php endif; ?>
                                >
                                <small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Number of publications</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Productivity']['Number of Publications'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Number_of_Publications']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> publication<?php if($papers_num > 1 || $papers_num == 0) echo "s"; ?> </span></small>

                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p style="margin: 0"><small> publications </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-xs-12 indicator-div" data-value="Number of Datasets" id="number-of-datasets">
                            <?php if(isset($element_config['Number of Datasets'])): ?>
                                <span class="indicator animate-indicator" data-target="<?= $datasets_num ?>">0</span>
                                <p
                                <?php if ($missing_papers_num > 0 && $facets_selected == false): ?> style="margin: 0" <?php endif; ?>
                                >
                                <small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Number of datasets</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Productivity']['Number of Datasets'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Number_of_Datasets']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> dataset<?php if($datasets_num > 1 || $datasets_num == 0) echo "s"; ?> </span></small>

                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p style="margin: 0"><small> datasets </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-xs-12 indicator-div" data-value="Number of Software" id="number-of-software">
                            <?php if(isset($element_config['Number of Software'])): ?>
                                <span class="indicator animate-indicator" data-target="<?= $software_num ?>">0</span>
                                <p
                                <?php if ($missing_papers_num > 0 && $facets_selected == false): ?> style="margin: 0" <?php endif; ?>
                                >
                                <small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Number of software</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Productivity']['Number of Software'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Number_of_Software']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> software</span></small>

                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p style="margin: 0"><small> software </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-xs-12 indicator-div" data-value="Number of Other Works" id="number-of-other-works">
                            <?php if(isset($element_config['Number of Other Works'])): ?>
                                <span class="indicator animate-indicator" data-target="<?= $other_num ?>">0</span>
                                <p
                                <?php if ($missing_papers_num > 0 && $facets_selected == false): ?> style="margin: 0" <?php endif; ?>
                                >
                                <small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Number of other</b>" data-content="<div><span class='green-bip'>Intuition:</span> <?= $indicators['Productivity']['Number of Other Works'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Number_of_Other_Works']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> other</span></small>

                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p style="margin: 0"><small> other </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($missing_papers_num > 0 && $facets_selected == false && !isset($current_cv_narrative)): ?>
                            <span class="grey-text">
                                <small>
                                    + <?= $missing_papers_num ?> missing works <i class="fa fa-question-circle" aria-hidden="true" title="Works from ORCiD that BIP software was not able to retrieve."></i>
                                </small>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div id="openess-indicators" class="inner-well well indicators-panel indicators-container-1200-height">
                        <span class="legend">Open Science Practice Indicators</span>
                        <div class="col-md-3 col-xs-12 indicator-div" data-value="Open Access Share" id="open-access-share">
                            <?php if(isset($element_config['Open Access Share'])): ?>
                                <?php $open_access_share = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['open_percentage']?>
                                <span class="indicator animate-indicator" data-target="<?= $open_access_share ?>"><?= ($open_access_share === "-") ? "-" : 0 ?></span><?= ($open_access_share !== "-") ?  "<span class='indicator'><small>%</small></span>" : ""?>
                                <p>
                                    <small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Open Access Share</b>" data-content="<div><?php if ($works_num > 0) echo '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.' ?></em><div><span class='green-bip'>Intuition:</span> <?= $indicators['Open Science']['Open Access Share'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Open_Access_Share']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> open access share </span></small>
                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small> open access share </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-xs-12 indicator-div" data-value="Open Access Works" id="open-access-works">
                            <?php if(isset($element_config['Open Access Works'])): ?>
                                <?php $open_access_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['open_papers']?>
                                <span class="indicator animate-indicator" data-target="<?= $open_access_works ?>"><?= ($open_access_works === "-") ? "-" : 0 ?></span>
                                <p>
                                    <small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Open Access Works</b>" data-content="<div><?php if ($works_num > 0) echo '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.' ?></em><div><span class='green-bip'>Intuition:</span> <?= $indicators['Open Science']['Open Access Works'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Open_Access_Works']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> open access works </span></small>
                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small> open access works </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-xs-12 indicator-div" data-value="Open Access Popular Works" id="open-access-popular-works">
                            <?php if(isset($element_config['Open Access Popular Works'])): ?>
                                <?php $open_access_popular_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['popular_open_papers']?>
                                <span class="indicator animate-indicator" data-target="<?= $open_access_popular_works ?>"><?= ($open_access_popular_works === "-") ? "-" : 0 ?></span>
                                <p>
                                    <small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Open Access Popular Works</b>" data-content="<div><?php if ($works_num > 0) echo '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.' ?></em><div><span class='green-bip'>Intuition:</span> <?= $indicators['Open Science']['Open Access Popular Works'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Open_Access_Popular_Works']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> open access popular works </span></small>
                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small> open access popular works </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3 col-xs-12 indicator-div" data-value="Open Access Influential Works" id="open-access-influential-works">
                            <?php if(isset($element_config['Open Access Influential Works'])): ?>
                                <?php $open_access_influential_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['influential_open_papers']?>
                                <span class="indicator animate-indicator" data-target="<?= $open_access_influential_works ?>"><?= ($open_access_influential_works === "-") ? "-" : 0 ?></span>
                                <p>
                                    <small><span role="button" data-toggle="popover" data-placement="auto" title="<b>Open Access Influential Works</b>" data-content="<div><?php if ($works_num > 0) echo '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.' ?></em><div><span class='green-bip'>Intuition:</span> <?= $indicators['Open Science']['Open Access Influential Works'] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => 'Open_Access_Influential_Works']);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> open access influential works </span></small>
                                </p>
                            <?php else: ?>
                                <span class="light-grey-text">
                                    <span class="indicator">-</span>
                                    <p><small> open access influential works </span></small></p>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grey-text text-justify">
                <small>
                    * All BIP! Scholar profile indicators are calculated based on data and processes elaborated in the <a href="<?= Url::to(['site/indicators'])?>" target="_blank" class='green-bip'>Indicators page</a>.
                    Please read the respective descriptions carefully to learn about the proper interpretation and limitations of each indicator before using it for any purpose.
                </small>
                <?php if (isset($current_cv_narrative)): ?>
                    <div>
                    <small>
                        *  Please note that the indicator values displayed are calculated considering only the research works related to the selected narrative. For the respective values calculated considering the whole researcher CV, please click on the 'Overview' tab.
                    </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(isset($element_config['Fair Academic Age'])): ?>
        <?php
            Modal::begin([
                'header' => '<h4>Fair Academic Age</h4><p> Fair academic age considers parental leaves, career changes and other inactive periods in your academic career. You can determine such periods using this form and you fair academic age will be revised accordingly.</p>',
                'options' => ['class' => 'modal fade ' . (($edit_perm) ? 'edit_perm' : ''), 'id' => 'academic-age-datepicker-modal'],
                'size' => 'modal-lg',
            ]);
        ?>
            <table class="table table-striped">
                <thead>
                    <tr><th style = "border-bottom:none" colspan="3">Inactive Periods:</th></tr>
                    <?php
                        $style_no_rag_dates = (!empty($rag_data)) ? "display:none" : "";
                    ?>
                    <tr ><th id = "no-rag-dates" colspan="3" style="text-align:center;border:none;font-weight:unset; <?=$style_no_rag_dates ?>">No inactive periods have been determined</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($rag_data as $rag_row) {  ?>
                        <tr id = "rag_<?= $rag_row["id"] ?>" >
                            <td class = "col-xs-5" >
                                <?= $rag_row["description"]?>
                            </td>
                            <td class = "col-xs-5" >
                                <?= $rag_row["start_date"]?> to <?= $rag_row["end_date"]?>
                            </td>
                            <?php if ($edit_perm): ?>
                                <td class = "col-xs-1 text-right" >
                                    <button type="button" class="rag-delete btn btn-xs"><i role="button" class="fa-solid fa-xmark"></i></button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($edit_perm): ?>
                <form id = "Rag-form" autocomplete="off" style = "margin: 20px 0px">
                    <div class = "row" >
                        <div class="form-group bip-focus col-md-5">
                            <label for="RAG-Description">Reason:</label>
                            <select class="form-control" id="RAG-Description" name="Rag_Description">
                                <option value= "Parental Leave">Parental Leave</option>
                                <option value= "Long-term Illness">Long-term Illness</option>
                                <option value = "Career Change">Career Change</option>
                                <option value = "National Service">National Service</option>
                                <option value= "Other">Other</option>
                                <option value= "I do not want to determine">I do not want to determine</option>
                            </select>
                        </div>

                        <div class="form-group bip-focus col-md-7">
                            <label class="form-label">Time Ranges:</label>
                            <?= DatePicker::widget([
                                // 'size' => 'md',
                                'type' => DatePicker::TYPE_RANGE,
                                'name' => 'from_date',
                                'options' => [
                                    'class' => ['clear-readonly'],
                                    'placeholder' => 'Start date',
                                    'required'=>'',
                                    'readonly'=> ''],
                                'name2' => 'to_date',
                                'options2' => [
                                    'class' => ['clear-readonly'],
                                    'placeholder' => 'End date',
                                    'required'=>'',
                                    'readonly'=> '',],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd'
                                ]
                            ]); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success" style="outline: none;">Add</button>
                        <button type="reset" class="btn btn-default" style="outline: none;">Reset</button>
                    </div>

                </form>
            <?php endif; ?>
        <?php
            Modal::end();
        ?>
    <?php endif; ?>

</div>

