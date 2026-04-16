<?php

use yii\db\Migration;

/**
 * Minimal blog schema for BIP: posts table only.
 * No blog_category, no blog_comment, no blog_tag.
 *
 * If you previously ran other blog migrations that are now removed from the repo, clean the history first, e.g.:
 * DELETE FROM migration WHERE version IN (
 *   'm180406_201480_blog_init',
 *   'm260323_120000_create_blog_tables',
 *   'm260324_000000_blog_consolidate_single_category'
 * );
 */
class m260325_120000_create_blog_post_table extends Migration {
    public function safeUp() {
        $this->dropTableIfExists('{{%blog_comment}}');
        $this->dropTableIfExists('{{%blog_post}}');
        $this->dropTableIfExists('{{%blog_category}}');

        $this->createTable('{{%blog_post}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'content' => $this->text()->notNull(),
            'cover_image' => $this->string(255),
            'tags' => $this->string(255)->notNull()->defaultValue(''),
            'slug' => $this->string(128)->notNull(),
            'click' => $this->integer()->notNull()->defaultValue(0),
            'user_id' => $this->integer(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx_blog_post_status', '{{%blog_post}}', 'status');
        $this->createIndex('idx_blog_post_created_at', '{{%blog_post}}', 'created_at');
        $this->createIndex('idx_blog_post_user_id', '{{%blog_post}}', 'user_id');
        $this->createIndex('ux_blog_post_slug', '{{%blog_post}}', 'slug', true);

    }

    public function safeDown() {
        $this->dropTableIfExists('{{%blog_post}}');
    }

    private function dropTableIfExists(string $tableName): void {
        if ($this->db->schema->getTableSchema($tableName, true) !== null) {
            $this->dropTable($tableName);
        }
    }
}
