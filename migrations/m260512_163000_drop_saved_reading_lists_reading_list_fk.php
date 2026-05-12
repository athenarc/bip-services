<?php

use yii\db\Migration;

/**
 * Drops the ON DELETE CASCADE foreign key from
 * saved_reading_lists.reading_list_id -> reading_lists.id.
 *
 * Rationale: when an owner hard-deletes a reading_list, we want users who
 * had saved that list to still see it in their sidebar (greyed out, with a
 * "Deleted by the owner" label) until they explicitly unlink it. The
 * CASCADE behaviour previously removed those saved entries server-side
 * before the UI could ever show the orphan state. The application layer
 * (ReadingsController::actionList) already tolerates dangling
 * reading_list_id references by rendering an orphan stub.
 */
class m260512_163000_drop_saved_reading_lists_reading_list_fk extends Migration {
    public function safeUp() {
        $tableSchema = $this->db->schema->getTableSchema('{{%saved_reading_lists}}', true);

        if ($tableSchema === null) {
            return;
        }

        $foreignKeyNames = array_keys($tableSchema->foreignKeys);

        if (in_array('fk_saved_reading_lists_reading_list_id', $foreignKeyNames, true)) {
            $this->dropForeignKey('fk_saved_reading_lists_reading_list_id', '{{%saved_reading_lists}}');
        }
    }

    public function safeDown() {
        $tableSchema = $this->db->schema->getTableSchema('{{%saved_reading_lists}}', true);

        if ($tableSchema === null) {
            return;
        }

        // remove any orphaned rows that would now violate the FK before re-adding it
        $this->execute(
            'DELETE sr FROM {{%saved_reading_lists}} sr '
            . 'LEFT JOIN {{%reading_lists}} rl ON rl.id = sr.reading_list_id '
            . 'WHERE rl.id IS NULL'
        );

        $foreignKeyNames = array_keys($tableSchema->foreignKeys);

        if (! in_array('fk_saved_reading_lists_reading_list_id', $foreignKeyNames, true)) {
            $this->addForeignKey(
                'fk_saved_reading_lists_reading_list_id',
                '{{%saved_reading_lists}}',
                'reading_list_id',
                '{{%reading_lists}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }
    }
}
