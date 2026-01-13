<?php

use yii\db\Migration;

/**
 * Class m260113_072247_add_graph_entity_to_spaces_annotations.
 */
class m260113_072247_add_graph_entity_to_spaces_annotations extends Migration {
    public function safeUp() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column already exists
        if ($table !== null && ! isset($table->columns['graph_entity'])) {
            // Add graph_entity column after metadata_query
            $this->addColumn('{{%spaces_annotations}}', 'graph_entity', $this->string(255)->null()->after('metadata_query')->comment('Graph entity identifier'));
        }
    }

    public function safeDown() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column exists before dropping
        if ($table !== null && isset($table->columns['graph_entity'])) {
            $this->dropColumn('{{%spaces_annotations}}', 'graph_entity');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_072247_add_graph_entity_to_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
