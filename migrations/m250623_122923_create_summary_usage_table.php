<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%summary_usage}}`.
 */
class m250623_122923_create_summary_usage_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%summary_usage}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Optional index on user_id for faster queries
        $this->createIndex('idx_summary_usage_user_id', '{{%summary_usage}}', 'user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%summary_usage}}');
    }
}
