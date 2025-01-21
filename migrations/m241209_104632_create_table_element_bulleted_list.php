<?php

use yii\db\Migration;

/**
 * Class m241209_104632_create_table_element_bulleted_list
 */
class m241209_104632_create_table_element_bulleted_list extends Migration
{
 /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%element_bulleted_list}}', [
            'id' => $this->primaryKey(), // Primary key
            'element_id' => $this->integer()->notNull(), // Foreign key reference
            'title' => $this->string(255), // Title field
            'description' => $this->text(), // Description field
            'elements_number' => $this->integer(), // Integer field
        ]);

        // Create index for `element_id`
        $this->createIndex(
            '{{%idx-element_bulleted_list-element_id}}',
            '{{%element_bulleted_list}}',
            'element_id'
        );

        // Add foreign key for `element_id`
        $this->addForeignKey(
            '{{%fk-element_bulleted_list-element_id}}',
            '{{%element_bulleted_list}}',
            'element_id',
            '{{%elements}}', // Reference table
            'id', // Reference column
            'CASCADE', // On delete
            'CASCADE'  // On update
        );

        // Modify `type` column of `elements` table to include 'Bulleted List'
        $this->execute("
            ALTER TABLE {{%elements}}
            MODIFY COLUMN `type` ENUM('Facets', 'Indicators', 'Narrative', 'Contributions List', 'Section Divider', 'Dropdown', 'Bulleted List');
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

         // Revert `type` column modification in `elements` table
        $this->execute("
            ALTER TABLE {{%elements}}
            MODIFY COLUMN `type` ENUM('Facets', 'Indicators', 'Narrative', 'Contributions List', 'Section Divider', 'Dropdown');
        ");

        // Drop foreign key
        $this->dropForeignKey(
            '{{%fk-element_bulleted_list-element_id}}',
            '{{%element_bulleted_list}}'
        );

        // Drop index
        $this->dropIndex(
            '{{%idx-element_bulleted_list-element_id}}',
            '{{%element_bulleted_list}}'
        );

        // Drop table
        $this->dropTable('{{%element_bulleted_list}}');
    }
}
