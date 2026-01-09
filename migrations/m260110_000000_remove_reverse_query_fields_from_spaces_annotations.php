<?php

use yii\db\Migration;

/**
 * Class m260110_000000_remove_reverse_query_fields_from_spaces_annotations.
 */
class m260110_000000_remove_reverse_query_fields_from_spaces_annotations extends Migration {
    public function safeUp() {
        // Drop reverse_query column
        $this->dropColumn('spaces_annotations', 'reverse_query');

        // Drop reverse_query_count column
        $this->dropColumn('spaces_annotations', 'reverse_query_count');
    }

    public function safeDown() {
        // Restore reverse_query column
        $this->addColumn('spaces_annotations', 'reverse_query', $this->text()->null());

        // Restore reverse_query_count column
        $this->addColumn('spaces_annotations', 'reverse_query_count', $this->text()->null());
    }
}
