<?php

use yii\db\Migration;

/**
 * Re-adds the enable_like_dislike_annotations column to spaces if missing.
 */
class m251208_120000_add_enable_like_dislike_annotations_to_spaces extends Migration
{
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%spaces}}');
        if ($table === null || isset($table->columns['enable_like_dislike_annotations'])) {
            // Table missing or column already present—nothing to do.
            return;
        }

        $this->addColumn(
            '{{%spaces}}',
            'enable_like_dislike_annotations',
            $this->boolean()
                ->notNull()
                ->defaultValue(0)
                ->comment('Enable like/dislike buttons for annotations')
        );
    }

    public function safeDown()
    {
        $table = Yii::$app->db->schema->getTableSchema('{{%spaces}}');
        if ($table !== null && isset($table->columns['enable_like_dislike_annotations'])) {
            $this->dropColumn('{{%spaces}}', 'enable_like_dislike_annotations');
        }
    }
}

