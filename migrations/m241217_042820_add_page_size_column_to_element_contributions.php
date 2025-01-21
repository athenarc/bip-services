<?php

use yii\db\Migration;

/**
 * Class m241217_042820_add_page_size_column_to_element_contributions
 */
class m241217_042820_add_page_size_column_to_element_contributions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        // Add a column `page_size` of type INT with default value 30
        $this->addColumn('{{%element_contributions}}', 'page_size', $this->integer(11)->defaultValue(30));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the column `page_size` if rolling back the migration
        $this->dropColumn('{{%element_contributions}}', 'page_size');

    }

}
