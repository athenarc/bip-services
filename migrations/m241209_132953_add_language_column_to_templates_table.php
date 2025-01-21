<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%templates}}`.
 */
class m241209_132953_add_language_column_to_templates_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%templates}}', 'language', $this->string(10)->notNull()->defaultValue('en')->after('description'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%templates}}', 'language');
    }
}
