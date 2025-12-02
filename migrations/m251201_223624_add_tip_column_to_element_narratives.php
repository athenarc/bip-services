<?php

use yii\db\Migration;

/**
 * Class m251201_223624_add_tip_column_to_element_narratives
 */
class m251201_223624_add_tip_column_to_element_narratives extends Migration
{

    public function safeUp()
    {
        $this->addColumn(
            'element_narratives',
            'tip',
            $this->text()->defaultValue(null)->after('description')
        );
    }

    public function safeDown()
    {
        $this->dropColumn('element_narratives', 'tip');
    }
 
}
