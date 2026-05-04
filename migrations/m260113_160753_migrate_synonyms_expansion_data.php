<?php

use yii\db\Migration;

/**
 * Class m260113_160753_migrate_synonyms_expansion_data.
 */
class m260113_160753_migrate_synonyms_expansion_data extends Migration {
    /**
     * Migrates synonyms expansion data from spaces_annotations to spaces_synonyms_expansion table.
     * {@inheritdoc}
     */
    public function safeUp() {
        // Check if the new table exists
        $tableExists = $this->db->schema->getTableSchema('{{%spaces_synonyms_expansion}}') !== null;

        if (! $tableExists) {
            throw new \yii\base\Exception('Table spaces_synonyms_expansion must be created first');
        }

        // Check if source columns exist
        $sourceTable = $this->db->schema->getTableSchema('{{%spaces_annotations}}');

        if ($sourceTable === null) {
            return; // Source table doesn't exist, nothing to migrate
        }

        $hasPerformSearchExpansion = isset($sourceTable->columns['perform_search_expansion']);
        $hasExpansionField = isset($sourceTable->columns['expansion_field']);
        $hasGraphEntity = isset($sourceTable->columns['graph_entity']);
        $hasGraphEntityLabel = isset($sourceTable->columns['graph_entity_label']);

        if (! $hasPerformSearchExpansion || ! $hasExpansionField || ! $hasGraphEntity || ! $hasGraphEntityLabel) {
            // Columns don't exist yet, nothing to migrate
            return;
        }

        // Migrate data: copy rows where perform_search_expansion = 1
        $this->execute("
            INSERT INTO {{%spaces_synonyms_expansion}} (spaces_id, display_name, graph_entity, graph_entity_label, expansion_field, enabled)
            SELECT 
                spaces_id,
                COALESCE(name, 'Entity') as display_name,
                graph_entity,
                graph_entity_label,
                expansion_field,
                enabled
            FROM {{%spaces_annotations}}
            WHERE perform_search_expansion = 1
                AND expansion_field IS NOT NULL
                AND expansion_field != ''
                AND graph_entity IS NOT NULL
                AND graph_entity != ''
                AND graph_entity_label IS NOT NULL
                AND graph_entity_label != ''
        ");
    }

    public function safeDown() {
        // Delete migrated data (we can't reliably restore it to spaces_annotations)
        $this->delete('{{%spaces_synonyms_expansion}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_160753_migrate_synonyms_expansion_data cannot be reverted.\n";

        return false;
    }
    */
}
