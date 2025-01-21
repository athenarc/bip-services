<?php

use yii\db\Migration;

/**
 * Class m241018_113418_add_last_login_to_user_table
 */
class m241018_113418_add_last_login_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'last_login', $this->timestamp()->null()->after('timestamp'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'last_login');
    }

}
