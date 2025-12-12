<?php

use yii\db\Migration;

/**
 * Class m251117_125027_add_margins_to_section_divider
 */
class m251117_125027_add_margins_to_section_divider extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('element_section_divider', 'margin_top', $this->string(50)->after('bottom_padding'));
        $this->addColumn('element_section_divider', 'margin_right', $this->string(50)->after('margin_top'));
        $this->addColumn('element_section_divider', 'margin_bottom', $this->string(50)->after('margin_right'));
        $this->addColumn('element_section_divider', 'margin_left', $this->string(50)->after('margin_bottom'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('element_section_divider', 'margin_top');
        $this->dropColumn('element_section_divider', 'margin_right');
        $this->dropColumn('element_section_divider', 'margin_bottom');
        $this->dropColumn('element_section_divider', 'margin_left');
    }
}

