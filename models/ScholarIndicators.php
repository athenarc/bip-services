<?php

# IMPORTANT: this class is not used ! 
# Scholar indicators are computed with an external yii2 extension (see: yii2-scholar-indicators)
# Please see details on how to update the extension above here: 
# https://www.yiiframework.com/wiki/846/yii2-how-to-createdevelop-a-new-extension-using-composer-locally-without-version-control-or-git

namespace app\models;

use Yii;
use yii\base\Model;

class ScholarIndicators {

	public $citations;
	public $papers;
	private $impact_fields;
	private $impact_classes;
	private $work_types;

    public function __construct($impact_fields, $impact_classes, $work_types, $papers){
		$this->citations = array_column($papers, 'citation_count');
		$this->papers = $papers;
		$this->impact_fields = $impact_fields;
		$this->impact_classes = $impact_classes;
		$this->work_types = $work_types;
  	}

	public function work_types_num() {

		$types = [
			'papers' => 0,
			'datasets' => 0,
			'software' => 0,
			'other' => 0
		];

		foreach($this->papers as $paper) {
			if ($paper['type'] == $this->work_types['dataset']) {
				$types['datasets']++;
			} elseif ($paper['type'] == $this->work_types['software']) {
				$types['software']++;
			} elseif ($paper['type'] == $this->work_types['other']) {
				$types['other']++;
			} else {
				$types['papers']++;
			}
		}

		return $types;
	}

	public function citations_num() {
		return array_sum($this->citations);
	}

	public function h_index() {
	    
		if (array_sum($this->citations) == 0)
			return 0;
			
	    // sorts citations in DESC order
	    rsort($this->citations, SORT_NUMERIC);

		$arr = range(1, count($this->citations));

		// h-index is the max value of the element-wise min of the two arrays
		return max(array_map(function($a, $b) { return ($a < $b) ? $a : $b; }, $this->citations, $arr));
	}

	public function i10_index(){
		return array_reduce($this->citations, function($ret, $val) {
    		return $ret += $val >= 10;
		});
	}

	public function popular_works_count($all_works) {
		// count popular works (those not in the last class)

		$pop_class_field = 'pop_class';
		$last_impact_class = end($this->impact_classes);

		// works without scores-classes (eg datasets) are not taken into account
		// if no works have scores-classes null is returned
		return array_reduce(array_filter(array_column($all_works, $pop_class_field)), function($ret, $val) use ($last_impact_class){
    		return $ret += $val !== $last_impact_class;
		});


	}

	public function influential_works_count($all_works) {
		// count influential works (those not in the last class)

		$inf_class_field = 'inf_class';
		$last_impact_class = end($this->impact_classes);

		// works without scores (eg datasets) are not taken into account
		// if no works have scores null is returned
		return array_reduce(array_filter(array_column($all_works, $inf_class_field)), function($ret, $val) use ($last_impact_class){
    		return $ret += $val !== $last_impact_class;
		});
	}

	public function impulse_sum() {
		$imp_field = $this->impact_fields['impulse'];
		return array_sum(array_column($this->papers, $imp_field));
	}

	public function open_papers_percentage() {

		$open_papers_array = array_filter($this->papers, function ($item) {
			return $item['is_oa'] == 1;
		});
		
		$open_papers = count($open_papers_array);

		$closed_papers = count(array_filter($this->papers, function($item) {
			return $item['is_oa'] === '0';  
		}));

		$known_papers = $open_papers + $closed_papers;
		
		$open_percentage = ($known_papers == 0) ? "" : round(100*($open_papers/$known_papers),0);


		$influential_open_papers = $this->influential_works_count($open_papers_array) ?? 0;
		$popular_open_papers = $this->popular_works_count($open_papers_array) ?? 0;

		return [
				'open_percentage' => $open_percentage, 
				'known_papers' => $known_papers, 
				'open_papers' => $open_papers,
				'influential_open_papers' => $influential_open_papers,
				'popular_open_papers' => $popular_open_papers,
			];
	}

	public function get_paper_min_year(){
		$years = array_column($this->papers, 'year');

		// remove possible unknown years
		$years_clean = array_filter($years, function($value) {
			return isset($value) ;  
		});
	
		if (empty($years_clean)) {
			return null;
		}

		// map years greater than current year (if any) to current year
		foreach($years_clean as $key => $value) {
			$years_clean[$key] = ($value > date('Y')) ? date('Y') : $value;
		}

		return (empty($years_clean)) ? null : min($years_clean);
	}

	public function get_academic_age($min_year) {

		return (empty($min_year)) ? null : (date('Y') - $min_year);
	}

	public static function get_responsible_academic_age($academic_age, $rag_data, $min_year) {

        if(empty($min_year) || empty($academic_age)){
            return null;
        }

        if(empty($rag_data)){
            return $academic_age;
        }
        // combine overlapping date ranges
        $rag_data_ranges = self::mergeDateRanges($rag_data);

        if(empty($rag_data_ranges)){
            return $academic_age;
        }

		$date_now = new \DateTime(date('Y-m-d'));
		// echo $date_now->format('Y-m-d') . "\n";

		$min_date = $min_year . '-01-01';
		$date_paper_min = new \DateTime($min_date);

		$total_interval = $date_paper_min->diff($date_now)->format('%r%a days');

		$total_removed_interval = 0;
		foreach ($rag_data_ranges as $rag_row){
			$interval = 0;
			$start_date = new \Datetime($rag_row['start_date']);
			$end_date = new \Datetime($rag_row['end_date']);
			if ($end_date > $date_paper_min && $end_date <= $date_now){
				if($start_date >= $date_paper_min ){
					$interval = $start_date->diff($end_date)->format('%a');
				}else{
					$interval = $date_paper_min->diff($end_date)->format('%a');
				}

			}elseif($end_date > $date_now && $start_date < $date_now){
				$interval = $start_date->diff($date_now)->format('%a');				
			}
			$total_removed_interval +=  $interval;
	
		}

		if (empty($total_interval) || empty($total_removed_interval)){
            return $academic_age;
        }
        // output in days
		$total_remaining_interval = ($academic_age*365) - $total_removed_interval;
		
        // case where total_removed_interval is greater than $academic_age*365
        // total_removed_interval refers to the current day while,
        // academic age refers to the 1st day of the current year 
        $total_remaining_interval = ($total_remaining_interval <= 0) ? 0 : Yii::$app->formatter->asDecimal(($total_remaining_interval/365), 2);
	
        // output in years
		return $total_remaining_interval;
    }

	// keep only start_date, end_date keys 
	public static function keepOnlyDateRanges($rag_data)
	{   
		foreach ($rag_data as $rag_row){
			$tmp_array= array();
			$tmp_array['start_date'] = $rag_row['start_date'];
			$tmp_array['end_date'] = $rag_row['end_date'];

			$ranges[] = $tmp_array;
		}
		return $ranges;
	}

	// same days are removed from output (eg  'start_date' => '2020-01-01' 'end_date' => '2020-01-01')
	public static function mergeDateRanges($rag_data)
	{   
		$ranges = self::keepOnlyDateRanges($rag_data);
		$retVal = [];
		//sort date ranges by begin time
		usort($ranges, function ($a, $b) {
			return strcmp($a['start_date'], $b['start_date']);
		});
	
		$currentRange = [];
		foreach ($ranges as $range) {

			// bypass invalid value
			if ($range['start_date'] >= $range['end_date']) {
				continue;
			}
			
			//fill in the first element
			if (empty($currentRange)) {
				$currentRange = $range;
				continue;
			}
	
			if ($currentRange['end_date'] < $range['start_date']) {
				$retVal[] = $currentRange;
				$currentRange = $range;
			} elseif ($currentRange['end_date'] < $range['end_date']) {
				$currentRange ['end_date'] = $range['end_date'];
			}
		}
	
		if ($currentRange) {
			$retVal[] = $currentRange;
		}
	
		return $retVal;
	}

	public function compute($rag_data) {

		$work_types_num = $this->work_types_num();
		$citations_num = $this->citations_num();
		$h_index = $this->h_index();
		$i10_index = $this->i10_index();
		$popular_works_count = $this->popular_works_count($this->papers);
		$influential_works_count = $this->influential_works_count($this->papers);
		$impulse = $this->impulse_sum();
		$openness = $this->open_papers_percentage();
		$paper_min_year = $this->get_paper_min_year();
		$academic_age = $this->get_academic_age($paper_min_year);

        $responsible_academic_age = self::get_responsible_academic_age($academic_age, $rag_data, $paper_min_year);

		return [
			'work_types_num' => $work_types_num,
			'citations_num' => $citations_num,
			'h_index' => $h_index,
			'i10_index' => $i10_index,
			'popular_works_count' => $popular_works_count,
			'influential_works_count' => $influential_works_count,
			'impulse' => $impulse,
			'openness' => $openness,
			'paper_min_year' => intval($paper_min_year),
			'academic_age' => $academic_age,
			'responsible_academic_age' => floatval($responsible_academic_age)
		];
	}
}