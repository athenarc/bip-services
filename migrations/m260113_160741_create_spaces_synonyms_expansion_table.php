<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%spaces_synonyms_expansion}}`.
 */
class m260113_160741_create_spaces_synonyms_expansion_table extends Migration {
    public function safeUp() {
        $this->createTable('{{%spaces_synonyms_expansion}}', [
            'id' => $this->primaryKey(),
            'spaces_id' => $this->integer()->notNull()->comment('Foreign key to spaces table'),
            'display_name' => $this->string(255)->notNull()->comment('Display name (e.g., "Disease")'),
            'graph_entity' => $this->string(255)->notNull()->comment('Entity type in graph DB (e.g., "Disease")'),
            'graph_entity_label' => $this->string(255)->notNull()->comment('Field to match against (e.g., "name")'),
            'expansion_field' => $this->string(255)->notNull()->comment('Field to return (e.g., "synonyms")'),
            'enabled' => $this->boolean()->notNull()->defaultValue(1)->comment('Whether this expansion is enabled'),
        ]);

        // Create foreign key
        $this->addForeignKey(
            'fk-spaces_synonyms_expansion-spaces_id',
            '{{%spaces_synonyms_expansion}}',
            'spaces_id',
            '{{%spaces}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Create index for faster lookups
        $this->createIndex(
            'idx-spaces_synonyms_expansion-spaces_id',
            '{{%spaces_synonyms_expansion}}',
            'spaces_id'
        );
    }

    public function safeDown() {
        $this->dropForeignKey('fk-spaces_synonyms_expansion-spaces_id', '{{%spaces_synonyms_expansion}}');
        $this->dropTable('{{%spaces_synonyms_expansion}}');
    }
}
