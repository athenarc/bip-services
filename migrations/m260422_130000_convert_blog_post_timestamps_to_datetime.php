<?php

use yii\db\Migration;

class m260422_130000_convert_blog_post_timestamps_to_datetime extends Migration {
    public function safeUp() {
        $tableName = '{{%blog_post}}';
        $table = $this->db->schema->getTableSchema($tableName, true);

        if ($table === null) {
            return;
        }

        $createdAtColumn = $table->getColumn('created_at');
        $updatedAtColumn = $table->getColumn('updated_at');

        if ($createdAtColumn === null || $updatedAtColumn === null) {
            return;
        }

        $createdIsDatetime = stripos((string) $createdAtColumn->dbType, 'datetime') !== false;
        $updatedIsDatetime = stripos((string) $updatedAtColumn->dbType, 'datetime') !== false;

        if ($createdIsDatetime && $updatedIsDatetime) {
            return;
        }

        $this->addColumn($tableName, 'created_at_tmp', $this->dateTime()->notNull());
        $this->addColumn($tableName, 'updated_at_tmp', $this->dateTime()->notNull());

        $this->execute("
            UPDATE {$tableName}
            SET created_at_tmp = COALESCE(FROM_UNIXTIME(created_at), NOW()),
                updated_at_tmp = COALESCE(FROM_UNIXTIME(updated_at), NOW())
        ");

        $this->dropColumn($tableName, 'created_at');
        $this->dropColumn($tableName, 'updated_at');

        $this->renameColumn($tableName, 'created_at_tmp', 'created_at');
        $this->renameColumn($tableName, 'updated_at_tmp', 'updated_at');

        $this->createIndex('idx_blog_post_created_at', $tableName, 'created_at');
    }

    public function safeDown() {
        $tableName = '{{%blog_post}}';
        $table = $this->db->schema->getTableSchema($tableName, true);

        if ($table === null) {
            return;
        }

        $createdAtColumn = $table->getColumn('created_at');
        $updatedAtColumn = $table->getColumn('updated_at');

        if ($createdAtColumn === null || $updatedAtColumn === null) {
            return;
        }

        $createdIsInteger = stripos((string) $createdAtColumn->dbType, 'int') !== false;
        $updatedIsInteger = stripos((string) $updatedAtColumn->dbType, 'int') !== false;

        if ($createdIsInteger && $updatedIsInteger) {
            return;
        }

        $this->addColumn($tableName, 'created_at_tmp', $this->integer()->notNull());
        $this->addColumn($tableName, 'updated_at_tmp', $this->integer()->notNull());

        $this->execute("
            UPDATE {$tableName}
            SET created_at_tmp = COALESCE(UNIX_TIMESTAMP(created_at), UNIX_TIMESTAMP(NOW())),
                updated_at_tmp = COALESCE(UNIX_TIMESTAMP(updated_at), UNIX_TIMESTAMP(NOW()))
        ");

        $this->dropColumn($tableName, 'created_at');
        $this->dropColumn($tableName, 'updated_at');

        $this->renameColumn($tableName, 'created_at_tmp', 'created_at');
        $this->renameColumn($tableName, 'updated_at_tmp', 'updated_at');

        $this->createIndex('idx_blog_post_created_at', $tableName, 'created_at');
    }
}
