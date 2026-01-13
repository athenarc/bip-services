<?php

use yii\db\Migration;

/**
 * Class m260113_161644_rename_entity_name_to_display_name_in_spaces_synonyms_expansion.
 */
class m260113_161644_rename_entity_name_to_display_name_in_spaces_synonyms_expansion extends Migration {
    public function safeUp() {
        $table = $this->db->schema->getTableSchema('{{%spaces_synonyms_expansion}}');

        // Check if table exists and has the entity_name column
        if ($table !== null && isset($table->columns['entity_name'])) {
            $this->renameColumn('{{%spaces_synonyms_expansion}}', 'entity_name', 'display_name');
        }
    }

    public function safeDown() {
        $table = $this->db->schema->getTableSchema('{{%spaces_synonyms_expansion}}');

        // Check if table exists and has the display_name column
        if ($table !== null && isset($table->columns['display_name'])) {
            $this->renameColumn('{{%spaces_synonyms_expansion}}', 'display_name', 'entity_name');
        }
    }
}
