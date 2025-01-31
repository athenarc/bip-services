<?php

use app\components\ResultItem;

?>

<?php if (!empty($element_config["show_header"])): ?>
    <h3 style="display: inline-block;">
        List of works
    </h3>
<?php endif;?>

<?php if ($works_num > 0): ?>
    <div id="publications">
        <div id='results_tbl'>
            <?php foreach ($papers as $paper) {
                echo ResultItem::widget([
                    "impact_indicators" => $impact_indicators,
                    "internal_id" => $paper["internal_id"],
                    "edit_perm" => $edit_perm,
                    "doi" => $paper["doi"],
                    "dois_num" => $paper["dois_num"],
                    "openaire_id" => $paper["openaire_id"],
                    "title" => $paper["title"],
                    "authors" => $paper["authors"],
                    "journal" => $paper["journal"],
                    "year" => $paper["year"],
                    "concepts" => $paper["concepts"],
                    "relations" => $paper["relations"],
                    "tags" => $paper["tags"],
                    "involvements" => Yii::$app->params['involvement_fields'],
                    "involved" => $paper["involvement"],
                    "pop_score" => $paper["attrank"],
                    "inf_score" => $paper["pagerank"],
                    "imp_score" => $paper["3y_cc"],
                    "cc_score" => $paper["citation_count"],
                    "pop_class" => $paper["pop_class"],
                    "inf_class" => $paper["inf_class"],
                    "imp_class" => $paper["imp_class"],
                    "cc_class" => $paper["cc_class"],
                    "is_oa" => $paper["is_oa"],
                    "type" => $paper["type"],
                    "show" => [
                        "concepts" => true,
                        "relations" => true,
                        "tags" => false,
                        "involvement" => true,
                    ],
                    "for_print" => true
                ]);
            } ?>
        </div>
    </div>
<?php else: ?>
    <div>BIP! software was not able to retrieve any publications for your profile. Also note that BIP Scholar retrieves only public works from your ORCiD profile</div>
<?php endif; ?>


<?php if ($missing_papers_num > 0 && $facets_selected == false && !isset($current_cv_narrative)): ?>
    <div id="missing-publications">
        <h3>
            <span role="button" title="Missing works" data-content="<div><span class='green-bip'></span><?= "This list contains works retrieved from ORCiD that BIP! software do not contain in its database" ?></div>">Missing works</span>
        </h3>
        <table>
            <tbody>
                <?php foreach ($missing_papers as $paper): ?>
                    <tr>
                        <td>
                            <!-- title -->
                            <div <?php if (isset($paper["title"]) && strlen($paper["title"]) > 90) { ?> title="<?= $paper['title'] ?>" <?php } ?>>
                                <?= (!isset($paper["title"])) ? 'N/A' : Yii::$app->bipstring->shortenString($paper["title"], 90) ?>
                            </div>

                            <div>

                                <!-- venue -->
                                <span <?php if (isset($paper["journal"]) && strlen($paper["journal"]) > 60) { ?> title="<?= $paper['journal'] ?>" <?php } ?>>
                                    <?= (!isset($paper["journal"]) || trim($paper["journal"]) == '') ? 'N/A' : Yii::$app->bipstring->shortenString($paper["journal"], 60)?>
                                </span>&middot;

                                <!-- year -->
                                <span>
                                    <?= (!isset($paper["year"]) || $paper["year"] == 0) ? "N/A" : $paper["year"] ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>