<?php

use yii\db\Migration;

class m260506_101000_add_sort_order_to_reading_lists extends Migration {
    public function safeUp() {
        $this->addColumn('{{%reading_lists}}', 'sort_order', $this->integer()->notNull()->defaultValue(0));
        $this->createIndex('idx_reading_lists_user_id_sort_order', '{{%reading_lists}}', ['user_id', 'sort_order']);

        $userIds = (new \yii\db\Query())
            ->select('user_id')
            ->from('{{%reading_lists}}')
            ->groupBy('user_id')
            ->column();

        foreach ($userIds as $userId) {
            $listIds = (new \yii\db\Query())
                ->select('id')
                ->from('{{%reading_lists}}')
                ->where(['user_id' => $userId])
                ->orderBy(['id' => SORT_ASC])
                ->column();

            $position = 1;
            foreach ($listIds as $listId) {
                $this->update('{{%reading_lists}}', ['sort_order' => $position], ['id' => $listId]);
                $position++;
            }
        }
    }

    public function safeDown() {
        $this->dropIndex('idx_reading_lists_user_id_sort_order', '{{%reading_lists}}');
        $this->dropColumn('{{%reading_lists}}', 'sort_order');
    }
}
