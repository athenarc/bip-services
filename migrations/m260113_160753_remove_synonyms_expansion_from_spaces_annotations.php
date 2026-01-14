<?php

use yii\db\Migration;

/**
 * Class m260113_160753_remove_synonyms_expansion_from_spaces_annotations.
 */
class m260113_160753_remove_synonyms_expansion_from_spaces_annotations extends Migration {
    /**
     * Removes synonyms expansion fields from spaces_annotations table.
     * {@inheritdoc}
     */
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
    }

    public function safeDown() {
        // Re-add the columns
        $table = $this->db->schema->getTableSchema('{{%spaces_annotations}}');

        if ($table === null) {
            return;
        }

        if (! isset($table->columns['perform_search_expansion'])) {
            $this->addColumn('{{%spaces_annotations}}', 'perform_search_expansion', $this->boolean()->notNull()->defaultValue(0)->after('metadata_fields'));
        }

        if (! isset($table->columns['expansion_field'])) {
            $this->addColumn('{{%spaces_annotations}}', 'expansion_field', $this->string(255)->null()->after('perform_search_expansion'));
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_160753_remove_synonyms_expansion_from_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
