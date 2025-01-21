<?php

use yii\db\Migration;

/**
 * Class m250121_170832_add_heading_type_column_to_element_tables
 */
class m250121_170832_add_heading_type_column_to_element_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%element_narratives}}', 'heading_type', $this->string(10)->defaultValue(''));
        $this->addColumn('{{%element_contributions}}', 'heading_type', $this->string(10)->defaultValue(''));
        $this->addColumn('{{%element_bulleted_list}}', 'heading_type', $this->string(10)->defaultValue(''));
        $this->addColumn('{{%element_dropdown}}', 'heading_type', $this->string(10)->defaultValue(''));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%element_narratives}}', 'heading_type');
        $this->dropColumn('{{%element_contributions}}', 'heading_type');
        $this->dropColumn('{{%element_bulleted_list}}', 'heading_type');
        $this->dropColumn('{{%element_dropdown}}', 'heading_type');
    }
}