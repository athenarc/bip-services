<?php

use yii\db\Migration;

/**
 * Class m260113_123221_remove_metadata_query_from_spaces_annotations.
 */
class m260113_123221_remove_metadata_query_from_spaces_annotations extends Migration {
    public function safeUp() {
        $this->dropColumn('{{%spaces_annotations}}', 'metadata_query');
    }

    public function safeDown() {
        // Re-add the column as TEXT (nullable) to match original structure
        $this->addColumn('{{%spaces_annotations}}', 'metadata_query', $this->text()->null()->after('query'));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_123221_remove_metadata_query_from_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
