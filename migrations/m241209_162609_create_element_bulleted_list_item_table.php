<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%element_bulleted_list_item}}`.
 */
class m241209_162609_create_element_bulleted_list_item_table extends Migration
{
    public function safeUp()
    {
        // Create the new table
        $this->createTable('element_bulleted_list_item', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'template_id' => $this->integer()->notNull(),
            'element_id' => $this->integer()->notNull(),
            'value' => "TEXT NOT NULL DEFAULT ''",
            'last_updated' => $this->timestamp()->null(),
        ]);

        // Add a foreign key constraint to the `element` table
        $this->addForeignKey(
            'fk-element_bulleted_list_item-element_id',
            'element_bulleted_list_item',
            'element_id',
            'elements',
            'id',
            'CASCADE'
        );

        // Optionally, add indexes for optimization
        $this->createIndex('idx-element_bulleted_list_item-element_id', 'element_bulleted_list_item', 'element_id');
        $this->createIndex('idx-element_bulleted_list_item-user_id', 'element_bulleted_list_item', 'user_id');
    }

    public function safeDown()
    {
        // Drop the foreign key
        $this->dropForeignKey('fk-element_bulleted_list_item-element_id', 'element_bulleted_list_item');

        // Drop the table
        $this->dropTable('element_bulleted_list_item');
    }
}
