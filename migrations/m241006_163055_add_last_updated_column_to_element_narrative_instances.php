<?php

use yii\db\Migration;

/**
 * Class m241006_163055_add_last_updated_column_to_element_narrative_instances
 */
class m241006_163055_add_last_updated_column_to_element_narrative_instances extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add `last_updated` column to your_table
        $this->addColumn('{{%element_narrative_instances}}', 'last_updated', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove `last_updated` column
        $this->dropColumn('{{%element_narrative_instances}}', 'last_updated');
    }
}
