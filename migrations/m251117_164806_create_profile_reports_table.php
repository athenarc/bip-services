<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%profile_reports}}`.
 */
class m251117_164806_create_profile_reports_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%profile_reports}}', [
            'id' => $this->primaryKey(),
            'reported_orcid' => $this->string(19)->notNull()->comment('ORCID of the reported profile'),
            'reporter_user_id' => $this->integer()->notNull()->comment('User ID of the person reporting'),
            'reason' => $this->string(255)->notNull()->comment('Reason for reporting'),
            'description' => $this->text()->comment('Additional details about the report'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('Status: pending, reviewed, resolved, dismissed'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Indexes for faster queries
        $this->createIndex('idx_profile_reports_reported_orcid', '{{%profile_reports}}', 'reported_orcid');
        $this->createIndex('idx_profile_reports_reporter_user_id', '{{%profile_reports}}', 'reporter_user_id');
        $this->createIndex('idx_profile_reports_status', '{{%profile_reports}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%profile_reports}}');
    }
}
