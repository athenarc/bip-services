<?php

use yii\db\Migration;

/**
 * Drops redundant user_id index from reported_scholar_topics.
 * user_id is already the leftmost column of the UNIQUE composite index.
 */
class m260430_193800_drop_reported_scholar_topics_user_id_index extends Migration {
    public function safeUp() {
        $this->dropIndex('idx_reported_scholar_topics_user_id', '{{%reported_scholar_topics}}');
    }

    public function safeDown() {
        $this->createIndex('idx_reported_scholar_topics_user_id', '{{%reported_scholar_topics}}', 'user_id');
    }
}
