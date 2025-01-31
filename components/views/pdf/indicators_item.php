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

<div class="well profile" style="padding-top: 25px;">
    <?php foreach ($filtered_semantics as $i_sem): ?>
        <div class="indicator-column">
            <div class="legend"><?= $i_sem ?> Indicators</div>
            <div id="<?= strtolower(str_replace(" ", "-", $i_sem)) ?>-indicators" class="inner-well well indicators-panel">    
                <?php 
                    $hasHiddenIndicators = false;
                    foreach ($element_config as $indicator_item): 
                        if ($indicator_item['indicator']['semantics'] == $i_sem): 
                            if ($indicator_item['status'] != 'Hidden'): ?>
                                <?php if ($indicator_item['status'] != 'Disabled'): ?>
                                    <div class="indicator-div" ?>
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
                                                    break;
                                                case "Open Access Works":
                                                    $open_access_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['open_papers'];
                                                    $data_target = $open_access_works;
                                                    break;
                                                case "Open Access Popular Works":
                                                    $open_access_popular_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['popular_open_papers'];
                                                    $data_target = $open_access_popular_works;
                                                    break;
                                                case "Open Access Influential Works":
                                                    $open_access_influential_works = ($works_num == 0 || $openness['known_papers'] == 0) ? "-" :  $openness['influential_open_papers'];
                                                    $data_target = $open_access_influential_works;
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

                                        <span class="indicator">
                                                <?= ($data_target === null) ? "-" : $data_target ?>
                                        </span><?= $data_target_span; ?>
                                        <div class="indicator-text">
                                            <small>
                                                <span title="<b> <?= $indicator_item['indicator']['name'] ?></b>"> 
                                                    <?= $icon; ?> <?= str_replace("number of", "", strtolower($indicator_item['indicator']['name'])) ?> 
                                                </span>
                                            </small><?= $custom_logic ?>
                                        </div>
                                    </div>
                                <?php endif ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>