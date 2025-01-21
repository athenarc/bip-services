<?php

use yii\db\Migration;

/**
 * Class m241119_142106_drop_assessment_tables
 */
class m241119_142106_drop_assessment_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Drop the foreign key first
        $this->dropForeignKey('assessment_protocols_ibfk_1', 'assessment_protocols');
        $this->dropForeignKey('protocol_indicators_ibfk_1', 'protocol_indicators');

        $this->dropTable('assessment_protocols');
        $this->dropTable('assessment_frameworks');
        $this->dropTable('protocol_indicators');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241119_142106_drop_assessment_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241119_142106_drop_assessment_tables cannot be reverted.\n";

        return false;
    }
    */
}
