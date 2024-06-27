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

    public $popular_works_count;
    public $influential_works_count;
    public $citations;
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

    public $current_cv_narrative;

    public $element_config;


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
        $data = Indicators::find(['level' => 'Researcher'])
        ->select(['name', 'semantics', 'intuition'])
        ->all();
        foreach ($data as $element) {
            if (!isset($indicators[$element['semantics']])) {
                $indicators[$element['semantics']] = [];
            }
            $indicators[$element['semantics']][$element['name']] = $element['intuition'];
        }

        return $this->render('indicators_item', [
            'indicators' => $indicators,
            'edit_perm' => $this->edit_perm,
            'works_num' => $this->works_num,
            'missing_papers_num' => $this->missing_papers_num,
            'facets_selected' => $this->facets_selected,
            'popular_works_count' => $this->popular_works_count,
            'influential_works_count' => $this->influential_works_count,
            'citations' => $this->citations,
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
        ]);
    }

}

?>