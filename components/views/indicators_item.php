<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;

?>
<?php
    $indicator_semantics = [];
    $indicator_status = [];
    foreach ($element_config as $config) {
        $indicator_semantics[] = $config['indicator']['semantics'];
        $indicator_status[] = $config['status'];
        $semantics_order[] = $config['semantics_order'];
    }

    // Reorder the array by 'indicator_order'
    uasort($element_config, function($a, $b) {
        return $a['indicator_order'] <=> $b['indicator_order'];
    });

    array_multisort($semantics_order, SORT_ASC, $indicator_semantics);
    $indicator_semantics = array_unique($indicator_semantics);

    // Filter out semantics groups with no "Enabled" indicators
    $filtered_semantics = [];
    foreach ($indicator_semantics as $i_sem) {
        $hasVisibleIndicators = false;
        foreach ($element_config as $indicator_item) {
            if ($indicator_item['indicator']['semantics'] == $i_sem && $indicator_item['status'] != 'Disabled') {
                $hasVisibleIndicators = true;
                break;
            }
        }

        if ($hasVisibleIndicators) {
            $filtered_semantics[] = $i_sem;
        }
    }
?>

<?php if(count($filtered_semantics) != 0): ?>
    <div class="row">
        <div class="col-xs-12 text-center" >
            <div class="well profile" style="padding-top: 25px;">
                <?php foreach ($filtered_semantics as $i_sem): ?>
                    <div class="col-md-3 col-xs-12">
                        <div id="<?= strtolower(str_replace(" ", "-", $i_sem)) ?>-indicators" class="inner-well well indicators-panel">
                            <span class="legend"><?= $i_sem ?> Indicators</span>
                            
                            <?php 
                                $hasHiddenIndicators = false;
                                foreach ($element_config as $indicator_item): 
                                    if ($indicator_item['indicator']['semantics'] == $i_sem): 
                                        if ($indicator_item['status'] != 'Hidden'): ?>
                                            <?php if ($indicator_item['status'] != 'Disabled'): ?>
                                                <div class="col-md-6 col-xs-12 indicator-div" 
                                                     data-value="<?= $indicator_item['indicator']['name'] ?>"
                                                     id=<?=strtolower(str_replace(" ", "-", $indicator_item['indicator']['name'])) ?>>

                                                    <?php 
                                                        $data_target_span = "";
                                                        $popup_span = "";
                                                        $icon = "";
                                                        $custom_logic = "";

                                                        switch ($indicator_item['indicator']['name']) {
                                                            case "Popular Works" :
                                                                $data_target = $popular_works_count;
                                                                $icon = "<i class='fa fa-fire' aria-hidden='true'></i>";
                                                                break;
                                                            case "Influential Works":
                                                                $data_target = $influential_works_count; 
                                                                $icon = "<i class='fa fa-university' aria-hidden='true'></i>";
                                                                break;
                                                            case "Citations":
                                                                $citations_value = ($works_num == 0) ? "-" : $citations;
                                                                $data_target = $citations_value; 
                                                                $icon = "<i class='fa fa-quote-left' aria-hidden='true'></i>";
                                                                break;
                                                            case "Aggregated Impulse":
                                                                $impulse_value = ($works_num == 0) ? "-" : $impulse;
                                                                $data_target = $impulse_value; 
                                                                $icon = "<i class='fa fa-rocket' aria-hidden='true'></i>";
                                                                break;
                                                            case "Aggregated Popularity":
                                                                $popularity_value = ($works_num == 0) ? "-" : $popularity;
                                                                $data_target = ($works_num == 0) ? "-" : $popularity["number"];
                                                                $data_target_span = ($works_num == 0) ? "" : "<span class='indicator'>" . $popularity["exponent"]. "</span>";
                                                                break;
                                                            case "Aggregated Influence":
                                                                $influence_value = ($works_num == 0) ? "-" : $influence;
                                                                $data_target = ($works_num == 0) ? "-" : $influence["number"];
                                                                $data_target_span = ($works_num == 0) ? "" : "<span class='indicator'>" . $influence["exponent"]. "</span>";
                                                                break;
                                                            case "h-index":
                                                                $data_target = $h_index; 
                                                                break;
                                                            case "i10-index":
                                                                $data_target = $i10_index; 
                                                                break;
                                                            case "Number of Publications":
                                                                $data_target = $papers_num; 
                                                                break;
                                                            case "Number of Datasets":
                                                                $data_target = $datasets_num; 
                                                                break;
                                                            case "Number of Software":
                                                                $data_target = $software_num; 
                                                                break;
                                                            case "Number of Other Works":
                                                                $data_target = $other_num; 
                                                                break;
                                                            case "Open Access Share":
                                                                $open_access_share = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['open_percentage'];
                                                                $data_target = $open_access_share;
                                                                $data_target_span = ($open_access_share !== "-") ? "<span class='indicator'><small>%</small></span>" : "";
                                                                $popup_span = '<div>' . ($works_num > 0 ? '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.</em>' : '') . '</div>';
                                                                break;
                                                            case "Open Access Works":
                                                                $open_access_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['open_papers'];
                                                                $data_target = $open_access_works;
                                                                $popup_span = '<div>' . ($works_num > 0 ? '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.</em>' : '') . '</div>';
                                                                break;
                                                            case "Open Access Popular Works":
                                                                $open_access_popular_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['popular_open_papers'];
                                                                $data_target = $open_access_popular_works;
                                                                $popup_span = '<div>' . ($works_num > 0 ? '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.</em>' : '') . '</div>';
                                                                break;
                                                            case "Open Access Influential Works":
                                                                $open_access_influential_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['influential_open_papers'];
                                                                $data_target = $open_access_influential_works;
                                                                $popup_span = '<div>' . ($works_num > 0 ? '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.</em>' : '') . '</div>';
                                                                break;
                                                            case "Academic Age":
                                                                $academic_age_value = (!empty($academic_age) || $academic_age === 0) ? $academic_age : "-";
                                                                $data_target = $academic_age_value;
                                                                break;
                                                            case "Fair Academic Age":
                                                                $responsible_academic_age_value = (isset($responsible_academic_age) && (!empty($academic_age) || $academic_age === 0)) ? $responsible_academic_age : "-";
                                                                $data_target = $responsible_academic_age_value;
                                                                $custom_logic = '<span role="button" data-toggle="modal" data-target="#academic-age-datepicker-modal"><i class="fa-solid ' . ($edit_perm ? 'fa-pen-to-square' : 'fa-eye') . ' fa-xs"></i></span>';
                                                                break;
                                                        }
                                                    ?>

                                                    <span class="indicator animate-indicator" 
                                                          data-target="<?= $data_target ?>">
                                                          <?= ($data_target === null) ? "-" : 0 ?>
                                                    </span><?= $data_target_span; ?>
                                                    <p>
                                                        <small>
                                                            <span role="button" 
                                                                  data-toggle="popover" 
                                                                  data-placement="auto" 
                                                                  title="<b> <?= $indicator_item['indicator']['name'] ?></b>" 
                                                                  data-content="
                                                                    <?= $popup_span; ?>  
                                                                    <div>
                                                                        <span class='green-bip'>Intuition:</span> <?= $indicators[$i_sem][$indicator_item['indicator']['name']] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => str_replace(" ", "_", $indicator_item['indicator']['name'])]);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a>
                                                                    </div>"> 
                                                                <?= $icon; ?> <?= str_replace("number of", "", strtolower($indicator_item['indicator']['name'])) ?> 
                                                            </span>
                                                        </small><?= $custom_logic ?>
                                                    </p>
                                                </div>
                                            <?php endif ?>
                                        <?php else: ?>
                                            <?php $hasHiddenIndicators = true; ?>
                                            <div class="col-md-6 col-xs-12 indicator-div hidden-indicator" style="display: none;"
                                                 data-value="<?= $indicator_item['indicator']['name'] ?>"
                                                 id="<?= strtolower(str_replace(" ", "-", $indicator_item['indicator']['name'])) ?>">

                                                <?php 
                                                    $data_target_span = "";
                                                    $popup_span = "";
                                                    $icon = "";
                                                    $custom_logic = "";

                                                    switch ($indicator_item['indicator']['name']) {
                                                        case "Popular Works" :
                                                            $data_target = $popular_works_count;
                                                            $icon = "<i class='fa fa-fire grey-text' aria-hidden='true'></i>";
                                                            break;
                                                        case "Influential Works":
                                                            $data_target = $influential_works_count; 
                                                            $icon = "<i class='fa fa-university grey-text' aria-hidden='true'></i>";
                                                            break;
                                                        case "Citations":
                                                            $citations_value = ($works_num == 0) ? "-" : $citations;
                                                            $data_target = $citations_value; 
                                                            $icon = "<i class='fa fa-quote-left grey-text' aria-hidden='true'></i>";
                                                            break;
                                                        case "Aggregated Impulse":
                                                            $impulse_value = ($works_num == 0) ? "-" : $impulse;
                                                            $data_target = $impulse_value; 
                                                            $icon = "<i class='fa fa-rocket grey-text' aria-hidden='true'></i>";
                                                            break;
                                                        case "Aggregated Popularity":
                                                            $popularity_value = ($works_num == 0) ? "-" : $popularity;
                                                            $data_target = ($works_num == 0) ? "-" : $popularity["number"];
                                                            $data_target_span = ($works_num == 0) ? "" : "<span class='indicator grey-text'>" . $popularity["exponent"]. "</span>";
                                                            break;
                                                        case "Aggregated Influence":
                                                            $influence_value = ($works_num == 0) ? "-" : $influence;
                                                            $data_target = ($works_num == 0) ? "-" : $influence["number"];
                                                            $data_target_span = ($works_num == 0) ? "" : "<span class='indicator grey-text'>" . $influence["exponent"]. "</span>";
                                                            break;
                                                        case "h-index":
                                                            $data_target = $h_index; 
                                                            break;
                                                        case "i10-index":
                                                            $data_target = $i10_index; 
                                                            break;
                                                        case "Number of Publications":
                                                            $data_target = $papers_num; 
                                                            break;
                                                        case "Number of Datasets":
                                                            $data_target = $datasets_num; 
                                                            break;
                                                        case "Number of Software":
                                                            $data_target = $software_num; 
                                                            break;
                                                        case "Number of Other Works":
                                                            $data_target = $other_num; 
                                                            break;
                                                        case "Open Access Share":
                                                            $open_access_share = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['open_percentage'];
                                                            $data_target = $open_access_share;
                                                            $data_target_span = ($open_access_share !== "-") ? "<span class='indicator grey-text'><small>%</small></span>" : "";
                                                            $popup_span = '<div>' . ($works_num > 0 ? '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.</em>' : '') . '</div>';
                                                            break;
                                                        case "Open Access Works":
                                                            $open_access_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['open_papers'];
                                                            $data_target = $open_access_works;
                                                            $popup_span = '<div>' . ($works_num > 0 ? '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.</em>' : '') . '</div>';
                                                            break;
                                                        case "Open Access Popular Works":
                                                            $open_access_popular_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['popular_open_papers'];
                                                            $data_target = $open_access_popular_works;
                                                            $popup_span = '<div>' . ($works_num > 0 ? '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.</em>' : '') . '</div>';
                                                            break;
                                                        case "Open Access Influential Works":
                                                            $open_access_influential_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['influential_open_papers'];
                                                            $data_target = $open_access_influential_works;
                                                            $popup_span = '<div>' . ($works_num > 0 ? '<em>The access mode is known for ' . $openness['known_papers'] . ' (out of ' . $works_num . ' in total) works.</em>' : '') . '</div>';
                                                            break;
                                                        case "Academic Age":
                                                            $academic_age_value = (!empty($academic_age) || $academic_age === 0) ? $academic_age : "-";
                                                            $data_target = $academic_age_value;
                                                            break;
                                                        case "Fair Academic Age":
                                                            $responsible_academic_age_value = (isset($responsible_academic_age) && (!empty($academic_age) || $academic_age === 0)) ? $responsible_academic_age : "-";
                                                            $data_target = $responsible_academic_age_value;
                                                            $custom_logic = '<span role="button" data-toggle="modal" data-target="#academic-age-datepicker-modal"><i class="fa-solid ' . ($edit_perm ? 'fa-pen-to-square' : 'fa-eye') . ' fa-xs grey-text"></i></span>';
                                                            break;
                                                    }
                                                ?>
                                                    
                                                <span class="indicator grey-text" data-target="<?= $data_target ?>"><?= ($data_target === null || $data_target === "-") ? "-" : $data_target ?></span><?= $data_target_span; ?>
                                                <p>
                                                    <small>
                                                        <span role="button" 
                                                                data-toggle="popover" 
                                                                data-placement="auto" 
                                                                title="<b> <?= $indicator_item['indicator']['name'] ?></b>" 
                                                                data-content="
                                                                <?= $popup_span; ?>  
                                                                <div>
                                                                    <span class='green-bip'>Intuition:</span> <?= $indicators[$i_sem][$indicator_item['indicator']['name']] ?> <a target='_blank' class='green-bip' href='<?= Url::toRoute(['site/indicators', '#' => str_replace(" ", "_", $indicator_item['indicator']['name'])]);?>'> <i class='fa fa-external-link-square' aria-hidden='true'></i></a>
                                                                </div>"> 
                                                                <?= $icon; ?> <span class="grey-text"><?= str_replace("number of", "", strtolower($indicator_item['indicator']['name'])) ?></span>
                                                        </span>
                                                    </small><?= $custom_logic ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <?php if ($hasHiddenIndicators): ?>
                                    <div class="col-md-12 col-xs-12 text-center grey-text" style="margin-top: 10px; margin-bottom: 10px;">
                                        <small><a href="#" class="show-more grey-link" data-target="<?= strtolower(str_replace(" ", "-", $i_sem)) ?>-indicators">Show more</a></small>
                                        <small><a href="#" class="show-less grey-link" data-target="<?= strtolower(str_replace(" ", "-", $i_sem)) ?>-indicators" style="display: none;">Show less</a></small>
                                    </div>
                                <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="col-md-12 col-xs-12 grey-text text-justify">
                    <?php if ($missing_papers_num > 0 && $facets_selected == false && !isset($current_cv_narrative)): ?>
                        <div class="row">
                            <div class="col-md-12 col-xs-12">
                                <span class="grey-text">
                                    <small>
                                        + <?= $missing_papers_num ?> missing work<?= ($missing_papers_num > 1) ? 's' : '' ?> <i class="fa fa-question-circle" aria-hidden="true" title="Works from ORCiD that BIP software was not able to retrieve."></i>
                                    </small>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
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
<?php endif ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.show-more').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                var target = this.getAttribute('data-target');
                document.querySelectorAll('#' + target + ' .hidden-indicator').forEach(function(hiddenIndicator) {
                    hiddenIndicator.style.display = 'block';
                });
                this.style.display = 'none';
                document.querySelector('.show-less[data-target="' + target + '"]').style.display = 'inline';
            });
        });

        document.querySelectorAll('.show-less').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                var target = this.getAttribute('data-target');
                document.querySelectorAll('#' + target + ' .hidden-indicator').forEach(function(hiddenIndicator) {
                    hiddenIndicator.style.display = 'none';
                });
                this.style.display = 'none';
                document.querySelector('.show-more[data-target="' + target + '"]').style.display = 'inline';
            });
        });
    });
</script>

