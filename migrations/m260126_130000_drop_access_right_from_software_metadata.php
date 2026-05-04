<?php

use yii\db\Migration;

/**
 * Drops the `access_right` column from `{{%software_metadata}}` table.
 */
class m260126_130000_drop_access_right_from_software_metadata extends Migration {
    public function safeUp() {
        $this->dropColumn('{{%software_metadata}}', 'access_right');
    }

    public function safeDown() {
        $this->addColumn('{{%software_metadata}}', 'access_right', $this->string(50));
    }
}
