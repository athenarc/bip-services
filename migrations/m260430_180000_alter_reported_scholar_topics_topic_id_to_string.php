<?php

use yii\db\Migration;

/**
 * Alters reported_scholar_topics.topic_id from INT to VARCHAR(100)
 * so topic ids can be stored as opaque strings.
 */
class m260430_180000_alter_reported_scholar_topics_topic_id_to_string extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%reported_scholar_topics}}', 'topic_id', $this->string(100)->notNull());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%reported_scholar_topics}}', 'topic_id', $this->integer()->notNull());
    }
}

