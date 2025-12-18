<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%like_dislike_annotations}}` and adds `enable_like_dislike_annotations` column to `spaces` table.
 */
class m251124_124829_create_like_dislike_annotations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create like_dislike_annotations table
        $this->createTable('{{%like_dislike_annotations}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'space_url_suffix' => $this->string(255)->notNull()->comment('Space identifier'),
            'paper_id' => $this->integer()->notNull(),
            'annotation_name' => $this->string(255)->notNull()->comment('Annotation label/name'),
            'annotation_id' => $this->string(255)->notNull()->comment('Annotation identifier from knowledge graph'),
            'action' => "ENUM('like', 'dislike') NOT NULL",
        ]);

        // Create indexes
        // Unique index for lookups by all 4 fields (getUserVote, saveVote, deleteVote)
        $this->createIndex('idx_like_dislike_annotations_user_paper_annotation', '{{%like_dislike_annotations}}', ['user_id', 'paper_id', 'annotation_id', 'space_url_suffix'], true);
        // Composite index for getting all votes for a paper (actionGetUserAnnotationVotes)
        $this->createIndex('idx_like_dislike_annotations_user_paper_space', '{{%like_dislike_annotations}}', ['user_id', 'paper_id', 'space_url_suffix']);

        // Add enable_like_dislike_annotations column to spaces table if it doesn't exist
        $spacesTable = \Yii::$app->db->schema->getTableSchema('{{%spaces}}');
        if ($spacesTable !== null && !isset($spacesTable->columns['enable_like_dislike_annotations'])) {
            $this->addColumn('{{%spaces}}', 'enable_like_dislike_annotations', $this->boolean()->notNull()->defaultValue(0)->comment('Enable like/dislike buttons for annotations'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop column from spaces table if it exists
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces}}');
        if ($table !== null && isset($table->columns['enable_like_dislike_annotations'])) {
            $this->dropColumn('{{%spaces}}', 'enable_like_dislike_annotations');
        }
        
        // Drop table if it exists
        $annotationsTable = \Yii::$app->db->schema->getTableSchema('{{%like_dislike_annotations}}');
        if ($annotationsTable !== null) {
            $this->dropTable('{{%like_dislike_annotations}}');
        }
    }
}

