<?php

use yii\db\Migration;

/**
 * Class m250218_155253_add_desciption_to_divider_element
 */
class m250218_155253_add_desciption_to_divider_element extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('element_section_divider', 'description', $this->text()->after('title'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('element_section_divider', 'description');
    }
}