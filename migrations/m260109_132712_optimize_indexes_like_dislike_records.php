<?php

use yii\db\Migration;

/**
 * Optimizes indexes for like_dislike_records table based on actual query patterns.
 *
 * Query patterns:
 * 1. getUserVote/saveVote/deleteVote: WHERE user_id = ? AND paper_id = ? AND space_url_suffix = ? AND query = ? AND ordering = ?
 * 2. getUserVotesBatch: WHERE user_id = ? AND paper_id IN (?) AND space_url_suffix = ? AND query = ? AND ordering = ?
 * 3. getVoteCounts: WHERE paper_id = ? AND space_url_suffix = ? GROUP BY action
 */
class m260109_132712_optimize_indexes_like_dislike_records extends Migration {
    public function safeUp() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%like_dislike_records}}');

        if ($table === null) {
            return;
        }

        // Drop old index that is no longer optimal (use try-catch in case it doesn't exist)
        try {
            $this->dropIndex('idx_like_dislike_records_user_paper_space', '{{%like_dislike_records}}');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Keep the existing index for vote counts if it exists, as it's still useful
        // idx_like_dislike_records_paper_space_action remains for getVoteCounts()

        // Check if query column is TEXT (requires prefix length for indexing)
        $queryColumn = $table->columns['query'] ?? null;
        $isTextColumn = $queryColumn && ($queryColumn->type === 'text' || $queryColumn->dbType === 'text');

        if ($isTextColumn) {
            // For TEXT columns, MySQL requires a prefix length for indexes
            // Using 255 characters prefix which should be sufficient for query strings
            // Create UNIQUE index with prefix for query column
            $this->execute('CREATE UNIQUE INDEX idx_like_dislike_records_unique_vote ON {{%like_dislike_records}} (user_id, paper_id, space_url_suffix, query(255), ordering)');

            // Create batch lookup index with prefix for query column
            $this->execute('CREATE INDEX idx_like_dislike_records_batch_lookup ON {{%like_dislike_records}} (user_id, space_url_suffix, query(255), ordering, paper_id)');
        } else {
            // For VARCHAR or other types, create normal indexes
            // Create UNIQUE index for the most common lookup pattern
            // This covers: getUserVote, saveVote (find existing), deleteVote
            // Order: user_id, paper_id, space_url_suffix, query, ordering
            // This is UNIQUE because votes are unique per this combination
            $this->createIndex(
                'idx_like_dislike_records_unique_vote',
                '{{%like_dislike_records}}',
                ['user_id', 'paper_id', 'space_url_suffix', 'query', 'ordering'],
                true // unique
            );

            // Create index optimized for batch queries (getUserVotesBatch)
            // Order: user_id, space_url_suffix, query, ordering, paper_id
            // This allows efficient filtering by user/space/query/ordering, then paper_id IN clause
            $this->createIndex(
                'idx_like_dislike_records_batch_lookup',
                '{{%like_dislike_records}}',
                ['user_id', 'space_url_suffix', 'query', 'ordering', 'paper_id']
            );
        }
    }

    public function safeDown() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%like_dislike_records}}');

        if ($table === null) {
            return;
        }

        // Drop optimized indexes (use try-catch in case they don't exist)
        try {
            $this->dropIndex('idx_like_dislike_records_unique_vote', '{{%like_dislike_records}}');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        try {
            $this->dropIndex('idx_like_dislike_records_batch_lookup', '{{%like_dislike_records}}');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Restore old index (check if it doesn't already exist)
        try {
            $this->createIndex(
                'idx_like_dislike_records_user_paper_space',
                '{{%like_dislike_records}}',
                ['user_id', 'paper_id', 'space_url_suffix']
            );
        } catch (\Exception $e) {
            // Index already exists, skip
        }
    }
}
