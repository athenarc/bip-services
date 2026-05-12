<?php

use yii\db\Migration;

class m260511_140000_add_sort_order_to_saved_reading_lists extends Migration {
    public function safeUp() {
        $this->addColumn('{{%saved_reading_lists}}', 'sort_order', $this->integer()->notNull()->defaultValue(0));
        $this->createIndex(
            'idx_saved_reading_lists_user_id_sort_order',
            '{{%saved_reading_lists}}',
            ['user_id', 'sort_order']
        );

        $rows = $this->db->createCommand(
            'SELECT id, user_id FROM {{%saved_reading_lists}} ORDER BY user_id ASC, created_at ASC, id ASC'
        )->queryAll();

        $lastUserId = null;
        $position = 0;
        foreach ($rows as $row) {
            $uid = (int) $row['user_id'];
            if ($lastUserId !== $uid) {
                $lastUserId = $uid;
                $position = 1;
            } else {
                $position++;
            }
            $this->update('{{%saved_reading_lists}}', ['sort_order' => $position], ['id' => (int) $row['id']]);
        }
    }

    public function safeDown() {
        $this->dropIndex('idx_saved_reading_lists_user_id_sort_order', '{{%saved_reading_lists}}');
        $this->dropColumn('{{%saved_reading_lists}}', 'sort_order');
    }
}
