<?php

/*
 * Define the namespace of the widget
 */
namespace app\components;

/*
 * Includes
 */
use yii\base\Widget;
use app\models\Indicators;

/*
 * The widget class
 */
class IndicatorsItem extends Widget
{
    /*
     * Widget properties
     */


    public $edit_perm;
    public $works_num;
    public $missing_papers_num;
    public $facets_selected;
    public $show_missing_works = true;

    public $popular_works_count;
    public $influential_works_count;
    public $citations;
    public $popularity;
    public $influence;
    public $impulse;
    public $h_index;
    public $i10_index;
    public $academic_age;
    public $paper_min_year;
    public $responsible_academic_age;
    public $rag_data;
    public $papers_num;
    public $datasets_num;
    public $software_num;
    public $other_num;
    public $openness;
    public $for_print;

    public $current_cv_narrative;

    public $element_config;
    public $contributions_lists;
    public $contributions_indicators;
    public $template_elements;
    public $missing_papers;


    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
    }

    /*
     * Running the widget
     */
    public function run()
    {
        // handle indicators logic
        if ($this->for_print && $this->contributions_indicators !== null) {
            $indicatorItems = $this->element_config;
            $linkedListId = $indicatorItems[0]['linked_contribution_element_id'] ?? null;

            $indicatorsLocal = $linkedListId !== null && isset($this->contributions_indicators[$linkedListId])
                ? $this->contributions_indicators[$linkedListId]
                : [];

            $listResult = $linkedListId !== null && isset($this->contributions_lists[$linkedListId])
                ? $this->contributions_lists[$linkedListId]
                : null;

            $isLinkedUserDefined = false;
            if ($linkedListId !== null && $this->template_elements !== null) {
                foreach ($this->template_elements as $te2) {
                    if (($te2['type'] ?? null) === 'Contributions List' && ($te2['element_id'] ?? null) == $linkedListId) {
                        $isLinkedUserDefined = !empty($te2['config']['user_defined']) && (int)$te2['config']['user_defined'] === 1;
                        break;
                    }
                }
            }

            $hasLinkedSelection = false;
            if ($listResult) {
                $hasLinkedSelection = (
                    (!empty($listResult['selected_papers']) && count($listResult['selected_papers']) > 0) ||
                    (!empty($listResult['selected_papers_num']) && (int)$listResult['selected_papers_num'] > 0)
                );
            }

            if ($isLinkedUserDefined && !$hasLinkedSelection) {
                $indicatorsLocal = [
                    'works_num' => 0,
                    'missing_papers_num' => count($this->missing_papers ?? []),
                    'show_missing_papers' => $listResult['show_missing_papers'] ?? true,
                    'popular_works_count' => 0,
                    'influential_works_count' => 0,
                    'citations_num' => 0,
                    'popularity' => ['number' => 0, 'exponent' => 'e0'],
                    'influence' => ['number' => 0, 'exponent' => 'e0'],
                    'impulse' => 0,
                    'h_index' => 0,
                    'i10_index' => 0,
                    'academic_age' => '-',
                    'responsible_academic_age' => '-',
                    'paper_min_year' => 0,
                    'work_types_num' => [
                        'papers' => 0,
                        'datasets' => 0,
                        'software' => 0,
                        'other' => 0,
                    ],
                    'openness' => [],
                ];
            }

            // Override widget properties with computed values
            $this->works_num = $indicatorsLocal['works_num'] ?? 0;
            $this->missing_papers_num = $indicatorsLocal['missing_papers_num'] ?? 0;
            $this->show_missing_works = $indicatorsLocal['show_missing_papers'] ?? true;
            $this->popular_works_count = $indicatorsLocal['popular_works_count'] ?? 0;
            $this->influential_works_count = $indicatorsLocal['influential_works_count'] ?? 0;
            $this->citations = $indicatorsLocal['citations_num'] ?? 0;
            $this->popularity = $indicatorsLocal['popularity'] ?? ['number' => 0, 'exponent' => 'e0'];
            $this->influence = $indicatorsLocal['influence'] ?? ['number' => 0, 'exponent' => 'e0'];
            $this->impulse = $indicatorsLocal['impulse'] ?? 0;
            $this->h_index = $indicatorsLocal['h_index'] ?? 0;
            $this->i10_index = $indicatorsLocal['i10_index'] ?? 0;
            $this->academic_age = $indicatorsLocal['academic_age'] ?? '';
            $this->paper_min_year = $indicatorsLocal['paper_min_year'] ?? 0;
            $this->responsible_academic_age = $indicatorsLocal['responsible_academic_age'] ?? '';
            $this->papers_num = $indicatorsLocal['work_types_num']['papers'] ?? 0;
            $this->datasets_num = $indicatorsLocal['work_types_num']['datasets'] ?? 0;
            $this->software_num = $indicatorsLocal['work_types_num']['software'] ?? 0;
            $this->other_num = $indicatorsLocal['work_types_num']['other'] ?? 0;
            $this->openness = $indicatorsLocal['openness'] ?? [];
        }

        $data = Indicators::find(['level' => 'Researcher'])
        ->select(['name', 'semantics', 'intuition'])
        ->all();
        foreach ($data as $element) {
            if (!isset($indicators[$element['semantics']])) {
                $indicators[$element['semantics']] = [];
            }
            $indicators[$element['semantics']][$element['name']] = $element['intuition'];
        }

        $data = [
            'indicators' => $indicators,
            'edit_perm' => $this->edit_perm,
            'works_num' => $this->works_num,
            'missing_papers_num' => $this->missing_papers_num,
            'facets_selected' => $this->facets_selected,
            'show_missing_works' => $this->show_missing_works,
            'popular_works_count' => $this->popular_works_count,
            'influential_works_count' => $this->influential_works_count,
            'citations' => $this->citations,
            'popularity' => $this->popularity,
            'influence' => $this->influence,
            'impulse' => $this->impulse,
            'h_index' => $this->h_index,
            'i10_index' => $this->i10_index,
            'academic_age' => $this->academic_age,
            'paper_min_year' => $this->paper_min_year,
            'responsible_academic_age' => $this->responsible_academic_age,
            'rag_data' => $this->rag_data,
            'papers_num' => $this->papers_num,
            'datasets_num' => $this->datasets_num,
            'software_num' => $this->software_num,
            'other_num' => $this->other_num,
            'openness' => $this->openness,
            'current_cv_narrative' => $this->current_cv_narrative,
            'element_config' => $this->element_config,
        ];

        if ($this->for_print) {
            return $this->render('pdf/indicators_item', $data);    
        }
        return $this->render('indicators_item', $data);
    }

}

?>