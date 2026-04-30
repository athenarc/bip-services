<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%profile_template_feedback}}`.
 */
class m260430_090000_create_profile_template_feedback_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%profile_template_feedback}}', [
            'id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull()->comment('Template ID that feedback refers to'),
            'profile_orcid' => $this->string(19)->notNull()->comment('ORCID of profile where feedback was submitted'),
            'reporter_user_id' => $this->integer()->notNull()->comment('User ID of scholar submitting feedback'),
            'message' => $this->text()->notNull()->comment('Feedback message'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('Status: pending, resolved, denied'),
            'admin_note' => $this->text()->null()->comment('Optional admin note when resolving/denying feedback'),
            'resolved_by_user_id' => $this->integer()->null()->comment('Admin user id that resolved/denied feedback'),
            'resolved_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_profile_template_feedback_template_id', '{{%profile_template_feedback}}', 'template_id');
        $this->createIndex('idx_profile_template_feedback_profile_orcid', '{{%profile_template_feedback}}', 'profile_orcid');
        $this->createIndex('idx_profile_template_feedback_reporter_user_id', '{{%profile_template_feedback}}', 'reporter_user_id');
        $this->createIndex('idx_profile_template_feedback_status', '{{%profile_template_feedback}}', 'status');
        $this->createIndex('idx_profile_template_feedback_resolved_by_user_id', '{{%profile_template_feedback}}', 'resolved_by_user_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%profile_template_feedback}}');
    }
}
