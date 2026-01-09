<?php

use yii\db\Migration;

/**
 * Class m260110_120000_rename_reverse_query_info_to_metadata_query.
 */
class m260110_120000_rename_reverse_query_info_to_metadata_query extends Migration {
    public function safeUp() {
        // Rename reverse_query_info column to metadata_query
        $this->renameColumn('spaces_annotations', 'reverse_query_info', 'metadata_query');
    }

    public function safeDown() {
        // Restore original column name
        $this->renameColumn('spaces_annotations', 'metadata_query', 'reverse_query_info');
    }
}
