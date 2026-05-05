<?php

use yii\db\Migration;

/**
 * Adds enable_summary flag to element_contributions table.
 */
class m251204_120000_add_enable_summary_to_element_contributions extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%element_contributions}}',
            'enable_summary',
            $this->boolean()->notNull()->defaultValue(0)->after('compact_view')
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%element_contributions}}', 'enable_summary');
    }
}


