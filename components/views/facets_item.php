<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;

?>

<div class="row">
    <div class="col-xs-12">
        <div class="well profile">
            <div>
                <?php if (isset($current_cv_narrative)): ?>
                    <h4>
                        <div class = "flex-no-wrap justify-between">
                            <div id="current_cv_narrative_id" class = "sr-only"><?=$current_cv_narrative->id?></div>
                            <div id="current_cv_narrative_papers" class = "sr-only"><?=$current_cv_narrative->papers?></div>
                            <div style = "margin-right:5px">
                            <span id="current_cv_narrative_title"><?=$current_cv_narrative->title?></span>
                            <?php if ($edit_perm): ?>
                                <span title="This narrative is <?=(!$current_cv_narrative->is_public || !$researcher->is_public) ? "private" : "public" ?>, please go to your settings to toggle its visibility.<?= (!$current_cv_narrative->is_public || !$researcher->is_public) ? " Please also make sure that your Scholar profile is also public to allow public access to this narrative." : ""?>" class="grey-text"><?=(!$current_cv_narrative->is_public || !$researcher->is_public) ? '<i class="fa-solid
                                fa-eye-slash fa-xs"></i>' : '<i class="fa-solid fa-eye fa-xs"></i>' ?></span>
                            <?php endif; ?>

                            </div>

                            <?php if ($edit_perm): ?>
                                <div style = "flex-shrink:0">
                                <a id ="cv-narrative-edit-button" href="#" class="grey-link" title="Edit current CV narrative"><i class="fa-solid fa-pen fa-xs"></i></a>
                                    <a id ="cv-narrative-delete-button" href="<?= Url::to(['scholar/delete-cv-narrative/', 'selected_cv_narrative_id' => $current_cv_narrative->id]) ?>"  class="grey-link" title="Delete current CV narrative"><i class="fa-solid fa-trash fa-xs"></i></a>
                                </div>
                            <?php endif; ?>

                        </div>
                    </h4>
                    <div id="current_cv_narrative_description" class="tiny-mce-body-cv-narrative"><?=$current_cv_narrative->description?></div>

                <?php else : ?>
                    <?php if(isset($element_config['Topics'])): ?>
                        <div class="facet-row">
                            <div class="facet-header grey-text">
                                <i class="fa-solid fa-atom" aria-hidden="true"></i>
                                <strong>
                                    <span role="button" data-toggle="popover" data-placement="auto" title="<b>Topics</b>" data-content="<div><span class='green-bip'></span> Topics are abstract concepts that works are about. In particular, we use the (L2) topics from OpenAlex. <a target='_blank' class='green-bip' href='https://docs.openalex.org/api-entities/concepts'><br/>see more <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> Topics <i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></span>
                                    <?php if($element_config['Topics']['visualize_opt'] === 1): ?>
                                        <button id='viz-topics' type='button' data-toggle="modal" data-target="#topics-stats-modal" class='btn btn-xs btn-default main-green'>
                                            <span>Visualize <i class="fa-solid fa-chart-pie"></i></span>
                                        </button>
                                    <?php endif; ?>

                                </strong>
                                <?= (!empty($selected_topics) && $edit_perm) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'topics[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                            </div>
                            <input id='fct_field' name='fct_field' value='' type='hidden'/ form=<?=$formId?>>

                            <?php if (count($result["facets"]["topics"]["counts"]) == 0) { ?>
                                    <span id="topic-facet-items">-</span>
                            <?php } else {
                                    $counts = $result["facets"]["topics"]["counts"];

                                    echo Html::checkboxList('topics', $selected_topics, $result["facets"]["topics"]['options'], [
                                        'id' => 'topic-facet-items',
                                        'style' => ['display' => 'inline'],
                                        'item' => function ($index, $label, $name, $checked, $value) use ($counts, $edit_perm, $formId, $element_config) {
                                            $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                            $disabled = ($checked) ? '' : 'disabled=disabled';
                                            $badge_number = ($element_config['Topics']['numbers_opt'] === 1) ?  "<span class='badge badge-primary'>$counts[$value]</span>" : '';

                                            return "<button id='topic-$value' type='button' class='btn btn-xs $btn_class facet-item'>
                                                <input id='topic-$value-i' name='topics[]' value='$value' form=$formId type='hidden' $disabled/>
                                                $label $badge_number
                                            </button>";
                                        }
                                    ]);
                                }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php /*
                        <div class="facet-row">
                            <div class="facet-header">
                                <i class="fa fa-tags" aria-hidden="true" title="User-provided tags"></i> <strong>Tags</strong><?= (!empty($selected_tags)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'tags[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                            </div>

                            <?php if (count($result["facets"]["tags"]["counts"]) == 0) { ?>
                                    <span id="tag-facet-items">-</span>
                            <?php } else {
                                    $counts = $result["facets"]["tags"]["counts"];

                                    echo Html::checkboxList('tags', $selected_tags, $result["facets"]["tags"]['options'], [
                                        'id' => 'tag-facet-items',
                                        'style' => ['display' => 'inline'],
                                        'item' => function ($index, $label, $name, $checked, $value) use ($counts, $formId) {
                                            $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                            $disabled = ($checked) ? '' : 'disabled=disabled';

                                            return "<button id='tag-$value' type='button' class='btn btn-xs $btn_class facet-item'>
                                                <input id='tag-$value-i' name='tags[]' value='$value' form=$formId type='hidden' $disabled/>
                                                $label <span class='badge badge-primary'>$counts[$value]</span>
                                            </button>";
                                        }
                                    ]);
                                }
                            ?>
                        </div>
                    */ ?>
                    <?php if(isset($element_config['Roles'])): ?>
                        <div class="facet-row">
                            <div class="facet-header grey-text">
                                <i class="fa fa-briefcase" aria-hidden="true"></i>
                                <strong>
                                    <span role="button" data-toggle="popover" data-placement="auto" title="<b>CRediT roles</b>" data-content="<div><span class='green-bip'></span> Contribution Roles based on the CRediT taxonomy. These roles describe each contributor's specific contribution to the scholarly work. <a target='_blank' class='green-bip' href='https://credit.niso.org'><br/> see more <i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>"> CRediT roles <i class="fa fa-question-circle light-grey-link" aria-hidden="true"></i></span>
                                    <?php if($element_config['Roles']['visualize_opt'] === 1): ?>
                                        <button id='viz-topics' type='button' data-toggle="modal" data-target="#credit-stats-modal" class='btn btn-xs btn-default main-green'>
                                            <span>Visualize <i class="fa-solid fa-chart-pie"></i></span>
                                        </button>
                                    <?php endif; ?>
                                </strong>
                                <?= (!empty($selected_roles)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'roles[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                            </div>
                            <?php if (count($result["facets"]["roles"]["counts"]) == 0) { ?>
                                <span id="role-facet-items">-</span>
                            <?php } else {
                                    $counts = $result["facets"]["roles"]["counts"];

                                    echo Html::checkboxList('roles', $selected_roles, $result["facets"]["roles"]['options'], [
                                        'id' => 'role-facet-items',
                                        'style' => ['display' => 'inline'],
                                        'item' => function ($index, $label, $name, $checked, $value) use ($counts, $formId, $element_config) {
                                            $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                            $disabled = ($checked) ? '' : 'disabled=disabled';
                                            $badge_number = ($element_config['Roles']['numbers_opt'] === 1) ?  "<span class='badge badge-primary'>$counts[$value]</span>" : '';

                                            return "<button id='role-$value' type='button' class='btn btn-xs $btn_class facet-item'>
                                                <input id='role-$value-i' name='roles[]' value='$value' form=$formId type='hidden' $disabled/>
                                                $label $badge_number
                                            </button>";
                                        }
                                    ]);
                                }

                            ?>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($element_config['Availability'])): ?>
                        <div class="facet-row">
                            <div class="facet-header grey-text">
                                <i class="fas fa-lock-open" aria-hidden="true" title="Open access data"></i> <strong>Availability</strong><?= (!empty($selected_accesses)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'accesses[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                            </div>
                            <?php if (count($result["facets"]["accesses"]["counts"]) == 0) { ?>
                                <span id="access-facet-items">-</span>
                            <?php } else {
                                    $counts = $result["facets"]["accesses"]["counts"];


                                    echo Html::checkboxList('accesses', $selected_accesses, $result["facets"]["accesses"]['options'], [
                                        'id' => 'access-facet-items',
                                        'style' => ['display' => 'inline'],
                                        'item' => function ($index, $label, $name, $checked, $value) use ($counts, $formId, $element_config) {
                                            $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                            $disabled = ($checked) ? '' : 'disabled=disabled';
                                            $label = $label['name'];
                                            $badge_number = ($element_config['Availability']['numbers_opt'] === 1) ?  "<span class='badge badge-primary'>$counts[$value]</span>" : '';


                                            return "<button id='access-$value' type='button' class='btn btn-xs $btn_class facet-item'>
                                                <input id='access-$value-i' name='accesses[]' value='$value' form=$formId type='hidden' $disabled/>
                                                $label $badge_number
                                            </button>";
                                        }
                                    ]);
                                }
                            ?>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($element_config['Work type'])): ?>
                        <div class="facet-row">
                            <div class="facet-header grey-text">
                                <i class="fas fa-cube" aria-hidden="true" title="Work types"></i> <strong>Work type</strong><?= (!empty($selected_types)) ? ' <button type="button" class="btn btn-xs" onclick="clearFacet(\'types[]\')">clear <i role="button" class="fa-solid fa-xmark"></i></button>' : ''?><br/>
                            </div>
                            <?php if (count($result["facets"]["types"]["counts"]) == 0) { ?>
                                <span id="types-facet-items">-</span>
                            <?php } else {
                                    $counts = $result["facets"]["types"]["counts"];

                                    echo Html::checkboxList('types', $selected_types, $result["facets"]["types"]['options'], [
                                        'id' => 'type-facet-items',
                                        'style' => ['display' => 'inline'],
                                        'item' => function ($index, $label, $name, $checked, $value) use ($counts, $formId, $element_config) {
                                            $btn_class = ($checked) ? 'btn-success' : 'btn-default';
                                            $disabled = ($checked) ? '' : 'disabled=disabled';
                                            $label = $label['name'];
                                            $badge_number = ($element_config['Work type']['numbers_opt'] === 1) ?  "<span class='badge badge-primary'>$counts[$value]</span>" : '';
                                            return "<button id='type-$value' type='button' class='btn btn-xs $btn_class facet-item'>
                                                <input id='type-$value-i' name='types[]' value='$value' form=$formId type='hidden' $disabled/>
                                                $label $badge_number
                                            </button>";
                                        }
                                    ]);
                                }
                            ?>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php if(isset($element_config['Topics']) && $element_config['Topics']['visualize_opt'] === 1): ?>

        <?php
            Modal::begin(['options' => ['class' => 'modal fade', 'id' => 'topics-stats-modal'],
                            'header' => '<h4>Topics Radar Chart</h4>',
                            'size' => 'modal-md',
                            'closeButton' => [
                                'class' => 'fa-solid pull-right',
                            ],
                        ]);
        ?>
            <div>
                <div class="radar-container">
                    <canvas id="chart-topics"></canvas>
                </div>
                <script>
                    var topic_counts = <?= '["' . implode('", "', $result["facets"]["topics"]["counts"]) . '"]'?>;
                    var topic_labels = <?= '["' . implode('", "', $result["facets"]["topics"]["options"]) . '"]'?>;

                    topic_labels = topic_labels.map(item => item.split(' '));

                    render_radar_chart('chart-topics', topic_counts.slice(0, 10), topic_labels.slice(0, 10));
                </script>
            </div>
        <?php
            Modal::end();
        ?>

    <?php endif; ?>


    <?php if(isset($element_config['Roles']) && $element_config['Roles']['visualize_opt'] === 1): ?>

        <?php
            Modal::begin(['options' => ['class' => 'modal fade', 'id' => 'credit-stats-modal'],
                        'header' => '<h4>CRediT Roles Radar Chart</h4>',
                        'size' => 'modal-md',
                        'closeButton' => [
                            'class' => 'fa-solid pull-right',
                        ],
                    ]);
        ?>
            <div>
                <div class="radar-container">
                    <canvas id="chart-credit"></canvas>
                </div>
                <script>
                    var roles_counts = <?= '["' . implode('", "', $result["facets"]["roles"]["counts"]) . '"]'?>;
                    var roles_labels = <?= '["' . implode('", "', $result["facets"]["roles"]["options"]) . '"]'?>;

                    roles_labels = roles_labels.map(item => {
                        return item.split(/\b(?=\w{1,2}|\w+\-|\w+\b)/);
                    });

                    render_radar_chart('chart-credit', roles_counts.slice(0, 10), roles_labels.slice(0, 10));
                </script>
            </div>
        <?php
            Modal::end();
        ?>

    <?php endif; ?>

</div>

