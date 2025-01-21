<?php

use yii\db\Migration;

/**
 * Class m241025_153558_add_graph_db_system_to_spaces
 */
class m241025_153558_add_graph_db_system_to_spaces extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // Add the graph_db_system column with ENUM type
        $this->addColumn('spaces', 'graph_db_system', "ENUM('neo4j', 'avantgraph') NULL DEFAULT NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // Drop the graph_db_system column
        $this->dropColumn('spaces', 'graph_db_system');
    }
 
}
