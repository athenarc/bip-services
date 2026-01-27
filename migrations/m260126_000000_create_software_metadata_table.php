<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%software_metadata}}`.
 */
class m260126_000000_create_software_metadata_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%software_metadata}}', [
            'id' => $this->primaryKey(),
            'paper_id' => $this->integer()->notNull(),
            'code_repo' => $this->text(),
            'version' => $this->string(255),
            'license' => $this->string(100),
            'access_right' => $this->string(50),
        ]);

        // Indexes for faster queries
        $this->createIndex('idx_software_metadata_paper_id', '{{%software_metadata}}', 'paper_id');
        $this->createIndex('idx_software_metadata_license', '{{%software_metadata}}', 'license');
        $this->createIndex('idx_software_metadata_access_right', '{{%software_metadata}}', 'access_right');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%software_metadata}}');
    }
}
