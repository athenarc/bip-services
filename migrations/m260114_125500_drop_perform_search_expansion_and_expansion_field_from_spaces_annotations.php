<?php

use yii\db\Migration;

/**
 * Class m260114_125500_drop_perform_search_expansion_and_expansion_field_from_spaces_annotations.
 * Drops perform_search_expansion and expansion_field columns from spaces_annotations table.
 * Also adds description column if it doesn't exist.
 */
class m260114_125500_drop_perform_search_expansion_and_expansion_field_from_spaces_annotations extends Migration {
    public function safeUp() {
        $table = $this->db->schema->getTableSchema('{{%spaces_annotations}}');

        if ($table === null) {
            return; // Table doesn't exist
        }

        // Drop perform_search_expansion column if it exists
        if (isset($table->columns['perform_search_expansion'])) {
            $this->dropColumn('{{%spaces_annotations}}', 'perform_search_expansion');
        }

        // Drop expansion_field column if it exists
        if (isset($table->columns['expansion_field'])) {
            $this->dropColumn('{{%spaces_annotations}}', 'expansion_field');
        }

        // Add description column if it doesn't exist
        if (! isset($table->columns['description'])) {
            $this->addColumn('{{%spaces_annotations}}', 'description', $this->text()->null()->after('display_name_plural')->comment('Description'));
        }
    }

    public function safeDown() {
        $table = $this->db->schema->getTableSchema('{{%spaces_annotations}}');

        if ($table === null) {
            return;
        }

        // Drop description column if it exists
        if (isset($table->columns['description'])) {
            $this->dropColumn('{{%spaces_annotations}}', 'description');
        }

        // Re-add expansion_field column if it doesn't exist
        if (! isset($table->columns['expansion_field'])) {
            $this->addColumn('{{%spaces_annotations}}', 'expansion_field', $this->string(255)->null()->after('metadata_fields'));
        }

        // Re-add perform_search_expansion column if it doesn't exist
        if (! isset($table->columns['perform_search_expansion'])) {
            $this->addColumn('{{%spaces_annotations}}', 'perform_search_expansion', $this->boolean()->notNull()->defaultValue(0)->after('metadata_fields'));
        }
    }
}
