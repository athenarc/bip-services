<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%like_dislike_records}}` and adds `enable_like_dislike_records` column to `spaces` table.
 */
class m251120_135754_create_like_dislike_records_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create like_dislike_records table
        $this->createTable('{{%like_dislike_records}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'space_url_suffix' => $this->string(255)->notNull(),
            'query' => $this->text()->null(),
            'ordering' => $this->string(50)->null(),
            'paper_id' => $this->integer()->notNull(),
            'paper_rank' => $this->integer()->null(),
            'action' => "ENUM('like', 'dislike') NOT NULL",
        ]);

        // Create indexes
        $this->createIndex('idx_like_dislike_records_user_id', '{{%like_dislike_records}}', 'user_id');
        $this->createIndex('idx_like_dislike_records_paper_id', '{{%like_dislike_records}}', 'paper_id');
        $this->createIndex('idx_like_dislike_records_space', '{{%like_dislike_records}}', 'space_url_suffix');
        $this->createIndex('idx_like_dislike_records_action', '{{%like_dislike_records}}', 'action');
        $this->createIndex('idx_like_dislike_records_user_paper_space', '{{%like_dislike_records}}', ['user_id', 'paper_id', 'space_url_suffix']);

        // Add enable_like_dislike_records column to spaces table
        $this->addColumn('{{%spaces}}', 'enable_like_dislike_records', $this->boolean()->notNull()->defaultValue(0)->comment('Enable like/dislike buttons for records'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop column from spaces table
        $this->dropColumn('{{%spaces}}', 'enable_like_dislike_records');
        
        // Drop table
        $this->dropTable('{{%like_dislike_records}}');
    }
}

