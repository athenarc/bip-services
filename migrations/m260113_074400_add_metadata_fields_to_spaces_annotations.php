<?php

use yii\db\Migration;

/**
 * Class m260113_074400_add_metadata_fields_to_spaces_annotations.
 */
class m260113_074400_add_metadata_fields_to_spaces_annotations extends Migration {
    public function safeUp() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column already exists
        if ($table !== null && ! isset($table->columns['metadata_fields'])) {
            // Add metadata_fields column after graph_entity_label
            $this->addColumn('{{%spaces_annotations}}', 'metadata_fields', $this->string(500)->null()->after('graph_entity_label')->comment('Comma-separated list of metadata fields to display'));
        }
    }

    public function safeDown() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column exists before dropping
        if ($table !== null && isset($table->columns['metadata_fields'])) {
            $this->dropColumn('{{%spaces_annotations}}', 'metadata_fields');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_074400_add_metadata_fields_to_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
