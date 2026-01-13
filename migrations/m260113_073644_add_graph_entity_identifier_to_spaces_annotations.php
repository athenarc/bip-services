<?php

use yii\db\Migration;

/**
 * Class m260113_073644_add_graph_entity_identifier_to_spaces_annotations.
 */
class m260113_073644_add_graph_entity_identifier_to_spaces_annotations extends Migration {
    public function safeUp() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column already exists
        if ($table !== null && ! isset($table->columns['graph_entity_identifier'])) {
            // Add graph_entity_identifier column after graph_entity
            $this->addColumn('{{%spaces_annotations}}', 'graph_entity_identifier', $this->string(255)->null()->after('graph_entity')->comment('Graph entity identifier'));
        }
    }

    public function safeDown() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column exists before dropping
        if ($table !== null && isset($table->columns['graph_entity_identifier'])) {
            $this->dropColumn('{{%spaces_annotations}}', 'graph_entity_identifier');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_073644_add_graph_entity_identifier_to_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
