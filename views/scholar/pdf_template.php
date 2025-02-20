<?php
// Example HTML structure for the PDF
use yii\helpers\Html;
use app\components\FacetsItem;
use app\components\IndicatorsItem;
use app\components\ContributionsListItem;
use app\components\DropdownElement;
use app\components\SectionDivider;
use app\components\BulletedList;
use yii\helpers\ArrayHelper;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Courgette&family=Montserrat:wght@300;400">
    <link href="https://fonts.googleapis.com/css2?family=Sacramento&family=Playfair+Display&display=swap" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Nunito' rel='stylesheet'>
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.0/dist/css/bootstrap.min.css"> -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        :root {
            --main-color: #439d44;
            --darker-color: #347935;
            --lighter-color: #94d194;
            --transparent-color: #439d444d;
        }

        html {
            color: #14213d;
            font-size: 11pt;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            }
        body {
            color: #14213d;
            font-family: Nunito;
            font-size: 11pt;
            line-height: 1.6;
            margin: 0px;
            padding: 0px;

        }
        @page {
            size: A4;
            margin: 1.9cm 1.32cm 3.67cm 1.9cm; 
            @bottom-right {
                content: counter(page);
                font-size: 12px;
                color: #555;
            }
        }

        #bip-logo {
            width: 100%;
            text-align: center;
        }
        #bip-logo img {
            width: 40mm;
        }
        #researcher-name {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 3px;
            color: var(--main-color);
            width: 100%;
            text-align: center;
        }
        #researcher-orcid {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 3px;
            color: lightgray;
            width: 100%;
            text-align: center;
            margin: 0cm 0cm 1cm;
        }

        .main-heading {
            text-align: center;
        }
        .impact-icon {
            display: inline-block;
            position: relative;
            color: #ddd;
            line-height: 1;
        }
        h3 {
            color: var(--main-color);
            font-size: 14pt;
            margin-top: 10mm;
            margin-bottom: 0.7mm;
        }
        
        /* facets */
        .facets {
            background-color: #f5f5f5;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
            padding: 2mm;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
            page-break-inside: avoid;
        }
        .facets .section {
            width: 100%;
        }
        .facets .section .title {
            font-size: 12pt;
            color: grey;
            padding: 1mm 2mm 0mm;
        }
        .facets ul {
            list-style-type: none;
            padding: 0;
            margin: 1mm 0;
            display: flex;
            flex-wrap: wrap;
        }
        .facets ul li {
            display: inline-block;
            padding: 1mm 2mm;
            margin: 1mm 1mm;
            background-color: var(--main-color);
            color: white;
            border-radius: 10px;
            font-size: 8pt;
            line-height: 1.5    ;
            text-align: center;
            box-sizing: border-box;
            color: #777;
            background-color: #fff;
            border: 1px solid #ccc;
        }
        .facets ul li .count {
            background-color: #777;
            top: 0;
            padding: 1px 5px;
            border-radius: 10px;
            color: #fff;
        }

        .section-divider {
            page-break-inside: avoid;
        }
        .text-left {
            text-align: left;
        }

        .panel {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background-color: #fff;
            page-break-inside: avoid;
        }

        .panel-heading {
            background-color: #f5f5f5;
            border-bottom: 1px solid #ddd;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .panel-body {
            padding: 15px;
            font-size: 14px;
            color: #555;
        }

        .panel-footer {
            border-top: 1px solid #ddd;
            padding: 10px 15px;
            font-size: 12px;
            text-align: right;
            color: #777;
        }

        .grey-link, .grey-text {
            color: grey;
        }

        .result-footer {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 5px;
            align-items: center;
        }

        .tag-region {
            margin: 1mm 0;
        }

        .tag {
            background-color: white;
            border: 1px solid grey;
            color: grey;
            margin: 0.3mm;
            padding: 0.6mm 1.2mm 0.6mm;
            border-radius: 10px;
            font-size: 8pt;
        }

        a {
            text-decoration: none;
        }

        .main-green {
            color: var(--main-color);
        }

        hr {
            border-top: 1px solid #eee;
        }

        /* Impact Icons */
        .impact-icon {
            display: inline-block;
            position: relative;
            color: #ddd;
            line-height: 1;
        }

        .impact-icon:after {
            font-family: FontAwesome;
            position: absolute;
            left: 0;
            top: 0;
            overflow: hidden;
            color: var(--main-color);
        }

        .popularity-icon:after {
            content: "\f06d";
        }

        .influence-icon:after {
            content: "\f19c";
        }

        .impulse-icon:after {
            content: "\f135";
        }

        .cc-icon:after {
            content: "\f10d";
        }

        .impact-icon-A:after {
            width: 100%;
        }

        .impact-icon-B:after {
            width: 90%;
        }

        .impact-icon-C:after {
            width: 70%;
        }

        .impact-icon-D:after {
            width: 40%;
        }

        .impact-icon-E:after {
            width: 0%;
        }

        /* profile indicators */

        .concept-confidence-container {
            display: inline-block;
            position: relative;
            width: 1em;
            height: 1em;
            border-radius: 50%;
            background-color: #ddd;
            overflow: hidden;
            vertical-align: middle;
            margin-bottom: 2px;
        }

        .concept-confidence-fill {
            display: block;
            position: absolute;
            height: 100%;
            background-color: var(--main-color, #ff0000);
            top: 0;
            left: 0;
        }

        .well {
            min-height: 20px;
            padding: 4mm;
            background-color: #f5f5f5;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .05);
        }

        .inner-well {
            overflow: hidden;
        }

        .indicator-div {
            text-align: center;
            padding: 3mm;
            line-height: 1;
        }

        #impact-indicators {
            border-color: #5cb85c;
        }


        #productivity-indicators {
            border-color: #d9534f;
        }


        #career-stage-indicators {
            border-color: #5bc0de;
        }

        #open-science-indicators {
            border-color: #f0ad4e;
        }

        .indicator {
            font-size: 1.2em;
            font-weight: bold;
        }

        .well.indicators-panel {
            display: flex;
            flex-wrap: wrap;
            flex-direction: row;
            justify-content: space-evenly;
            align-items: center;
        }

        .legend {
            font-size: 14pt;
            padding: 2mm;
            font-family: 'Raleway', sans-serif;
            fill: #333333;
            text-align: center;
        }

        .indicator-column {
            page-break-inside: avoid;
            margin-bottom: 4mm;
        }

        .indicator-text {
            padding: 2mm;
        }
        
    </style>
    <title>PDF Export</title>
</head>
<body>
    <div id="bip-logo"><img src="data:image/jpeg;base64,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" alt=""></div>
    <div id="researcher-name"><?php echo strtoupper($researcher->name)?></div>
    <div id="researcher-orcid"><a class="grey-link" href="<?= "https://orcid.org/" . $researcher->orcid ?>"><i class="fa-brands fa-orcid"></i> <?php echo strtoupper($researcher->orcid)?></a></div>
    <?php
        foreach ($template_elements as $index => $element) {

            switch ($element["type"]) {
                case "Facets":?>
                    <div class="facets">
                        <?php
                            echo FacetsItem::widget([
                                'for_print' => true,
                                'result' => $result,
                                'formId' => 'scholar-form',
                                'selected_topics' => $selected_topics,
                                'selected_roles' => $selected_roles,
                                'selected_accesses' => $selected_accesses,
                                'selected_types' => $selected_types,
                                'current_cv_narrative' => null,
                                'researcher' => $researcher,
                                'element_config' => $element["config"]
                            ]);
                    ?>
                    </div>
                    <?php break;

                case "Indicators":
                    echo IndicatorsItem::widget([
                        'works_num' => $result["papers_num"],
                        'missing_papers_num' => count($missing_papers),
                        'facets_selected' => $facets_selected,
                        'popular_works_count' => $popular_works_count,
                        'influential_works_count' => $influential_works_count,
                        'citations' => $citations,
                        'popularity' => $popularity,
                        'influence' => $influence,
                        'impulse' => $impulse,
                        'h_index' => $h_index,
                        'i10_index' => $i10_index,
                        'academic_age' => $academic_age,
                        'paper_min_year' => $paper_min_year,
                        'responsible_academic_age' => $responsible_academic_age,
                        'rag_data' => $rag_data,
                        'papers_num' => $papers_num,
                        'datasets_num' => $datasets_num,
                        'software_num' => $software_num,
                        'other_num' => $other_num,
                        'openness' => $openness,
                        'current_cv_narrative' => null,
                        'element_config' => $element["config"],
                        'for_print'=> true
                    ]);

                    break;

                case "Contributions List":?>
                    <div class="contributions">
                        <?php
                            echo ContributionsListItem::widget([
                                'for_print' => true,
                                'impact_indicators' => $impact_indicators,
                                'facets_selected' => $facets_selected,
                                'result' => $result,
                                'papers' => $result["papers"],
                                'works_num' => $result["papers_num"],
                                'missing_papers' => $missing_papers,
                                'missing_papers_num' => count($missing_papers),
                                'sort_field' => $sort_field,
                                'orderings' => $orderings,
                                'formId' => 'scholar-form',
                                'current_cv_narrative' => null,
                                'element_config' => $element["config"]
                            ]);
                        ?>
                    </div>
                    <?php break;

                case "Narrative":?>

                    <div class="narrative">
                        <h3><?= $element["config"]->title ?></h3>
                        <?php if (!empty($element["config"]->value)): ?>
                            <div style="text-align: justify">
                                <?= $element["config"]->value ?>
                            </div>
                        <?php else: ?>
                            <?php if (!$element["config"]->hide_when_empty): ?>
                                <div class="alert alert-warning" role="alert">
                                    The researcher has not yet provided input for this element.
                                </div>
                            <?php endif ?>
                        <?php endif; ?>
                    </div>
                    <?php break;
                
                case "Section Divider":
                    echo SectionDivider::widget([
                        'index' => $index,
                        'element_id' => $element["element_id"],
                        'title' => $element["config"]->title,
                        'heading_type' => $element["config"]->heading_type,
                        'description' => $element["config"]->description,
                        'show_description_tooltip' => $element["config"]->show_description_tooltip,
                        'top_padding' => $element["config"]->top_padding,
                        'bottom_padding' => $element["config"]->bottom_padding,
                        'show_top_hr' => $element["config"]->show_top_hr,
                        'show_bottom_hr' => $element["config"]->show_bottom_hr,
                    ]);
                    break;
                
                case "Bulleted List":
                    echo BulletedList::widget([
                        'element_id' => $element["element_id"],
                        'title' => $element["config"]->title,
                        'description' => $element["config"]->description,
                        'items' => $element["config"]->items,
                        'for_print' => true
                    ]);
                    break;

                case "Dropdown":
                    echo DropdownElement::widget([
                        'index' => $index,
                        'element_id' => $element["element_id"],
                        'title' => $element["config"]->title,
                        'description' => $element["config"]->description,    
                        'hide_when_empty' => $element["config"]->hide_when_empty,
                        'elementDropdownOptionsArray' => ArrayHelper::map($element["config"]->elementDropdownOptions, 'id', 'option_name'),
                        'option_id' => $element["config"]->option_id,
                        'last_updated' => $element["config"]->last_updated,
                        'for_print' => true,
                    ]);
                    break;

                default:
                    break;
            }
        }
    ?>
</body>
</html>
