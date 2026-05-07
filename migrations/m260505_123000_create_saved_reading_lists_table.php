<?php

use yii\db\Migration;

class m260505_123000_create_saved_reading_lists_table extends Migration {
    public function safeUp() {
        if ($this->db->schema->getTableSchema('{{%saved_reading_lists}}', true) !== null) {
            $this->dropTable('{{%saved_reading_lists}}');
        }

        $this->createTable('{{%saved_reading_lists}}', [
            'id' => $this->primaryKey(),
            'reading_list_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'ux_saved_reading_lists_reading_list_id_user_id',
            '{{%saved_reading_lists}}',
            ['reading_list_id', 'user_id'],
            true
        );
        $this->createIndex('idx_saved_reading_lists_user_id', '{{%saved_reading_lists}}', 'user_id');

        $this->addForeignKey(
            'fk_saved_reading_lists_reading_list_id',
            '{{%saved_reading_lists}}',
            'reading_list_id',
            '{{%reading_lists}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        // NOTE:
        // users table is MyISAM in this environment, so MySQL/MariaDB cannot create
        // a foreign key pointing to it. Keep indexed user_id and enforce integrity
        // at the application level.
    }

    public function safeDown() {
        $tableSchema = $this->db->schema->getTableSchema('{{%saved_reading_lists}}', true);
        if ($tableSchema) {
            $foreignKeyNames = array_keys($tableSchema->foreignKeys);
            if (in_array('fk_saved_reading_lists_reading_list_id', $foreignKeyNames, true)) {
                $this->dropForeignKey('fk_saved_reading_lists_reading_list_id', '{{%saved_reading_lists}}');
            }
        }
        $this->dropTable('{{%saved_reading_lists}}');
    }
}
