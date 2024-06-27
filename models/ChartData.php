<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;

use app\models\ScholarIndicators;
use app\models\Orcid;

use yii\helpers\ArrayHelper;

class ChartData extends Model {
    
    public $chart_data = null;

    private static function map_classes_to_scores($classname) {
        return Yii::$app->params['impact_classes_to_chart_scores'][$classname];
    }

    private static function map_classes_to_tooltips($classname) {
        return Yii::$app->params['impact_classes'][$classname]['name'];
    }

    private static function format_chart_data($pop_class, $inf_class, $imp_class, $cc_class) {
        $overall_measures = [
            $pop_class, 
            $inf_class, 
            $cc_class,
            $imp_class 
        ];

        $data = array_map('self::map_classes_to_scores', $overall_measures);
        $tooltips = array_map('self::map_classes_to_tooltips', $overall_measures);

        return [
            'data' => $data,
            'tooltips' => $tooltips
        ];
    }

    public static function calculate($pop_class, $inf_class, $imp_class, $cc_class) {
        // format data for overall ranking/classes
        return ChartData::format_chart_data($pop_class, $inf_class, $imp_class, $cc_class);        
    }

    public static function calculateForConcepts($concepts) {
        $chart_data = [];

        // format data for topic-based impact ranking/classes
        foreach ($concepts as $key => $concept ) {
            $concept_data = ChartData::format_chart_data($concept["pop_class"], $concept["inf_class"], $concept["imp_class"], $concept["cc_class"]);
            array_push($chart_data, $concept_data);
        }

        return $chart_data;
    }
}