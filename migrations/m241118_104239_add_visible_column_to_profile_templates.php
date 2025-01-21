<?php

use yii\db\Migration;

/**
 * Class m241118_104239_add_visible_column_to_profile_templates
 */
class m241118_104239_add_visible_column_to_profile_templates extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('templates', 'visible', $this->boolean()->defaultValue(false)->notNull()->comment('Indicates if the template is visible'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('templates', 'visible');
    }
}
