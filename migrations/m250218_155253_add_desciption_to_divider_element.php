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
        $this->addColumn('element_section_divider', 'show_description_tooltip', $this->boolean()->defaultValue(true)->after('description'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('element_section_divider', 'description');
        $this->dropColumn('element_section_divider', 'show_description_tooltip');
    }
}