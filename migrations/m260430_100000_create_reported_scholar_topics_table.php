<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%reported_scholar_topics}}`.
 */
class m260430_100000_create_reported_scholar_topics_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%reported_scholar_topics}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'paper_id' => $this->integer()->notNull(),
            'topic_id' => $this->string(100)->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Prevent duplicate reports for the same user/scholar-topic tuple.
        $this->createIndex(
            'idx_reported_scholar_topics_unique',
            '{{%reported_scholar_topics}}',
            ['user_id', 'paper_id', 'topic_id'],
            true
        );
        $this->createIndex('idx_reported_scholar_topics_user_id', '{{%reported_scholar_topics}}', 'user_id');
        $this->createIndex('idx_reported_scholar_topics_paper_id', '{{%reported_scholar_topics}}', 'paper_id');
        $this->createIndex('idx_reported_scholar_topics_topic_id', '{{%reported_scholar_topics}}', 'topic_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%reported_scholar_topics}}');
    }
}
