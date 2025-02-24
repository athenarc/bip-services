<?php

use yii\db\Migration;

/**
 * Class m250224_141331_add_orcid_to_users
 */
class m250224_141331_add_orcid_to_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Adding the 'auth_provider' and 'auth_id' columns to the 'users' table
        $this->addColumn('{{%users}}', 'auth_provider', $this->string(50)->null()->after('email'));
        $this->addColumn('{{%users}}', 'auth_id', $this->string(255)->null()->after('auth_provider'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Dropping the 'auth_provider' and 'auth_id' columns from the 'users' table
        $this->dropColumn('{{%users}}', 'auth_provider');
        $this->dropColumn('{{%users}}', 'auth_id');
    }
}
