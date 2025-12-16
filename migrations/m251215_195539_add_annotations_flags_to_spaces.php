<?php

use yii\db\Migration;

/**
 * Class m251215_195539_add_annotations_flags_to_spaces
 */
class m251215_195539_add_annotations_flags_to_spaces extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn(
            '{{%spaces}}',
            'has_annotations_flag',
            $this->tinyInteger(1)->notNull()->defaultValue(0)->after('pubmed_types')
        );

        $this->addColumn(
            '{{%spaces}}',
            'enable_annotations_flag',
            $this->tinyInteger(1)->notNull()->defaultValue(0)->after('has_annotations_flag')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%spaces}}', 'enable_annotations_flag');
        $this->dropColumn('{{%spaces}}', 'has_annotations_flag');
    }

}
