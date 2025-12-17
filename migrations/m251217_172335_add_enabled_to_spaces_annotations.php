<?php

use yii\db\Migration;

/**
 * Class m251217_172335_add_enabled_to_spaces_annotations.
 */
class m251217_172335_add_enabled_to_spaces_annotations extends Migration {
    public function safeUp() {
        $this->addColumn(
            '{{%spaces_annotations}}',
            'enabled',
            $this->tinyInteger(1)->notNull()->defaultValue(1)->after('reverse_query_info')
        );
    }

    public function safeDown() {
        $this->dropColumn('{{%spaces_annotations}}', 'enabled');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251217_172335_add_enabled_to_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
