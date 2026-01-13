<?php

use yii\db\Migration;

/**
 * Class m260113_073153_add_description_to_spaces_annotations.
 */
class m260113_073153_add_description_to_spaces_annotations extends Migration {
    public function safeUp() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column already exists
        if ($table !== null && ! isset($table->columns['description'])) {
            // Add description column after display_name_plural
            $this->addColumn('{{%spaces_annotations}}', 'description', $this->text()->null()->after('display_name_plural')->comment('Description'));
        }
    }

    public function safeDown() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column exists before dropping
        if ($table !== null && isset($table->columns['description'])) {
            $this->dropColumn('{{%spaces_annotations}}', 'description');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_073153_add_description_to_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
