<?php

use yii\db\Migration;

/**
 * Class m260113_072950_rename_description_to_display_name_plural_in_spaces_annotations.
 */
class m260113_072950_rename_description_to_display_name_plural_in_spaces_annotations extends Migration {
    public function safeUp() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column exists before renaming
        if ($table !== null && isset($table->columns['description'])) {
            // Rename description column to display_name_plural
            $this->renameColumn('{{%spaces_annotations}}', 'description', 'display_name_plural');
        }
    }

    public function safeDown() {
        $table = \Yii::$app->db->schema->getTableSchema('{{%spaces_annotations}}');

        // Check if column exists before renaming back
        if ($table !== null && isset($table->columns['display_name_plural'])) {
            // Rename display_name_plural column back to description
            $this->renameColumn('{{%spaces_annotations}}', 'display_name_plural', 'description');
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_072950_rename_description_to_display_name_plural_in_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
