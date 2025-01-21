<?php

namespace app\models;

use Yii;
use yii\helpers\Url;

class GraphConnectionNeo4j extends GraphConnection {
    
    public function run($annotation_query, $params) {

        // Execute query with parameters
        $stats = $this->protocol->run($annotation_query, $params);

        // Pull records from last executed query
        $rows = $this->protocol->pull();
        
        // remove last row (avantgraph miscellaneous data)
        $rows = array_slice($rows, 0, -1);

        return [
            $stats, 
            $rows
        ];
    }

}