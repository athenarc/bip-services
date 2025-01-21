<?php

use yii\db\Migration;

/**
 * Class m241115_112632_add_visible_column_to_profile_template_categories
 */
class m241115_112632_add_visible_column_to_profile_template_categories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('profile_template_categories', 'visible', $this->boolean()->defaultValue(false)->notNull()->comment('Indicates if the category is visible'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('profile_template_categories', 'visible');
    }
}
