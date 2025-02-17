<?php

use yii\db\Migration;

/**
 * Class m250217_152252_expand_elements_title_length
 */
class m250217_152252_expand_elements_title_length extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Altering the 'title' column to VARCHAR(1024) in element_bulleted_list
        $this->alterColumn('element_bulleted_list', 'title', $this->string(1024));

        // Altering the 'title' column to VARCHAR(1024) in element_dropdown
        $this->alterColumn('element_dropdown', 'title', $this->string(1024));

        // Altering the 'title' column to VARCHAR(1024) in element_narratives
        $this->alterColumn('element_narratives', 'title', $this->string(1024));

        // Altering the 'title' column to VARCHAR(1024) in element_section_divider
        $this->alterColumn('element_section_divider', 'title', $this->string(1024));

        // Altering the 'title' column to VARCHAR(1024) in element_table
        $this->alterColumn('element_table', 'title', $this->string(1024));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Reverting the 'title' column back to its previous state (change the type as needed)
        $this->alterColumn('element_bulleted_list', 'title', $this->string(255));
        $this->alterColumn('element_dropdown', 'title', $this->string(255));
        $this->alterColumn('element_narratives', 'title', $this->string(255));
        $this->alterColumn('element_section_divider', 'title', $this->string(255));
        $this->alterColumn('element_table', 'title', $this->string(255));
    }
}