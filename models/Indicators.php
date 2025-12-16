<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "indicators".
 *
 * @property int $id
 * @property string $name
 * @property string|null $level
 * @property string|null $semantics
 * @property string|null $intuition
 * @property string|null $parameters
 * @property string|null $calculation
 * @property string|null $limitations
 * @property string|null $availability
 * @property string|null $code
 * @property string|null $references
 */
class Indicators extends \yii\db\ActiveRecord {
    public static function tableName() {
        return 'indicators';
    }

    public function rules() {
        return [
            [['name'], 'required'],
            [['level', 'semantics', 'intuition', 'parameters', 'calculation', 'limitations', 'availability', 'code', 'references'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'level' => 'Level',
            'semantics' => 'Semantics',
            'intuition' => 'Intuition',
            'parameters' => 'Parameters',
            'calculation' => 'Calculation',
            'limitations' => 'Limitations',
            'availability' => 'Availability',
            'code' => 'Code',
            'references' => 'References',
        ];
    }

    /**
     * Gets query for [[ElementIndicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementIndicators() {
        return $this->hasMany(ElementIndicators::class, ['indicator_id' => 'id']);
    }

    public static function getImpactIndicatorsAsArray($level) {
        if (isset($level) && is_string($level)) {
            $indicators = self::find()
                ->where(['level' => $level, 'semantics' => 'Impact'])
                ->select(['name', 'intuition'])
                ->asArray()
                ->all();

            if (is_array($indicators)) {
                $impact_indicators = ArrayHelper::map($indicators, 'name', 'intuition');

                return $impact_indicators;
            }
        }

        return [];
    }

    public function computeForPapers(array $papers, $rag_data = [], $missing_papers_num = 0) {
        $indicators = [];

        $impact_fields = Yii::$app->params['impact_fields'];
        $impact_classes = array_keys(Yii::$app->params['impact_classes']);

        $type_map = [
            '0' => 'papers',
            '1' => 'datasets',
            '2' => 'software',
            '3' => 'other',
        ];

        $work_types_num = ['papers' => 0, 'datasets' => 0, 'software' => 0, 'other' => 0];
        $citations = [];

        foreach ($papers as $p) {
            $citation = (int) ($p['citation_count'] ?? 0);
            $citations[] = $citation;

            $type_raw = $p['type'] ?? null;
            $type_key = $type_map[$type_raw] ?? 'other';
            $work_types_num[$type_key]++;
        }

        $score_thresholds = (new \yii\db\Query())
            ->select('*')
            ->from('low_category_scores_view')
            ->one();

        foreach ($papers as &$p) {
            $p['pop_class'] = \app\models\SearchForm::assignClass($p, 'attrank', $score_thresholds, 'popularity');
            $p['inf_class'] = \app\models\SearchForm::assignClass($p, 'pagerank', $score_thresholds, 'influence');
        }

        $scholar_indicators = new \app\models\ScholarIndicators($impact_fields, $impact_classes, $work_types_num, $papers);

        $indicators['works_num'] = count($papers);
        $indicators['missing_papers_num'] = $missing_papers_num;

        rsort($citations);
        $h = $i10 = 0;

        foreach ($citations as $i => $c) {
            if ($c >= $i + 1) {
                $h++;
            }

            if ($c >= 10) {
                $i10++;
            }
        }

        $indicators['popular_works_count'] = $scholar_indicators->popular_works_count($papers);
        $indicators['influential_works_count'] = $scholar_indicators->influential_works_count($papers);

        $indicators['citations_num'] = array_sum($citations);
        $indicators['h_index'] = $h;
        $indicators['i10_index'] = $i10;

        $indicators['popularity'] = $scholar_indicators->popularity_sum();
        $indicators['influence'] = $scholar_indicators->influence_sum();
        $indicators['impulse'] = $scholar_indicators->impulse_sum();

        $paper_min_year = $scholar_indicators->get_paper_min_year();
        $academic_age = $scholar_indicators->get_academic_age($paper_min_year);
        $responsible_academic_age = \app\models\ScholarIndicators::get_responsible_academic_age($academic_age, $rag_data, $paper_min_year);

        $indicators['paper_min_year'] = $paper_min_year;
        $indicators['academic_age'] = $academic_age;
        $indicators['responsible_academic_age'] = $responsible_academic_age;

        $indicators['work_types_num'] = $work_types_num;
        $indicators['openness'] = $scholar_indicators->open_papers_percentage();

        return $indicators;
    }
}
