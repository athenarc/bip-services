<?php

namespace app\models;

use Yii;


class GraphConnectionFactory {

    public static function createConnection($graph_db_system, $annotation_db) {
        switch ($graph_db_system) {
            case 'neo4j': 
                return new GraphConnectionNeo4j($annotation_db);
            case 'avantgraph':
                return new GraphConnectionAvantgraph($annotation_db);
            default:
                throw new yii\base\Exception('The requested graph database is not supported yet.');
        }
    }

}