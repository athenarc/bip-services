<?php

use yii\db\Migration;

/**
 * Adds annotation_type_id column to like_dislike_annotations table after paper_id
 */
class m260109_083208_add_annotation_type_id_to_like_dislike_annotations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = \Yii::$app->db->schema->getTableSchema('{{%like_dislike_annotations}}');
        
        // Check if column already exists
        if ($table !== null && !isset($table->columns['annotation_type_id'])) {
            // Drop the old unique index first
            $this->dropIndex('idx_like_dislike_annotations_user_paper_annotation', '{{%like_dislike_annotations}}');
            
            // Add annotation_type_id column after paper_id
            // Made nullable to handle existing records; new records will have it set via application logic
            $this->addColumn('{{%like_dislike_annotations}}', 'annotation_type_id', $this->integer()->null()->after('paper_id')->comment('Annotation type ID from spaces_annotations table'));
            
            // Recreate the unique index including annotation_type_id
            $this->createIndex('idx_like_dislike_annotations_user_paper_annotation', '{{%like_dislike_annotations}}', ['user_id', 'paper_id', 'annotation_type_id', 'annotation_id', 'space_url_suffix'], true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $table = \Yii::$app->db->schema->getTableSchema('{{%like_dislike_annotations}}');
        
        if ($table !== null && isset($table->columns['annotation_type_id'])) {
            // Drop the index
            $this->dropIndex('idx_like_dislike_annotations_user_paper_annotation', '{{%like_dislike_annotations}}');
            
            // Remove the column
            $this->dropColumn('{{%like_dislike_annotations}}', 'annotation_type_id');
            
            // Recreate the old unique index
            $this->createIndex('idx_like_dislike_annotations_user_paper_annotation', '{{%like_dislike_annotations}}', ['user_id', 'paper_id', 'annotation_id', 'space_url_suffix'], true);
        }
    }
}
