<?php

use yii\db\Migration;

/**
 * Class m260113_124426_add_search_expansion_fields_to_spaces_annotations
 */
class m260113_124426_add_search_expansion_fields_to_spaces_annotations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%spaces_annotations}}', 'perform_search_expansion', $this->boolean()->notNull()->defaultValue(0)->after('metadata_fields'));
        $this->addColumn('{{%spaces_annotations}}', 'expansion_field', $this->string(255)->null()->after('perform_search_expansion'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%spaces_annotations}}', 'expansion_field');
        $this->dropColumn('{{%spaces_annotations}}', 'perform_search_expansion');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260113_124426_add_search_expansion_fields_to_spaces_annotations cannot be reverted.\n";

        return false;
    }
    */
}
