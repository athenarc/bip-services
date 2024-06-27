<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;
use app\models\Researcher;


class ScholarSearchForm extends Model {
    
    public $keywords;
    public $ordering;

    public function __construct($keywords = null, $ordering = null) {

        parent::__construct();

        $this->keywords = $keywords;
        $this->ordering = $ordering;
    }

    public function formName() {
        return '';
    } 

    public function rules() {
        return [
            [['keywords'], 'required'],
            [['keywords'], 'string', 'min' => 3],
        ];
    }

    public function search() {
        
        // form keyword search query; search researcher name and orcid
        $query = Researcher::find()->andFilterWhere([ 'is_public' => true ]);
        
        if (!empty($this->keywords)) {
            
            $keywordsArray = explode(' ', $this->keywords);

            // initialize the condition array
            $conditions = ['OR'];

            // loop through each token and add a LIKE condition for each column
            foreach ($keywordsArray as $keyword) {
                $conditions[] = ['LIKE', 'name', $keyword];
                $conditions[] = ['LIKE', 'orcid', $keyword];
            }

            // build the final query condition
            $query->andFilterWhere($conditions);

        }

        // count all results needed for pagination
        $pagination = new Pagination([
            'totalCount' => $query->count()
        ]);

        // adjust query ordering
        if (!empty($this->ordering)) {
            $query->orderBy($this->ordering . ' ASC');
        }

        // fetch results in current page 
        $rows = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return [
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }
    
}