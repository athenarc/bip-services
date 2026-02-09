<?php

use yii\db\Migration;

/**
 * Adds enable_facet column to spaces_annotations.
 * When false, the annotation is hidden from sidebar "Show results with" and from Key annotations (dropdown and pills).
 */
class m260206_000000_add_enable_facet_to_spaces_annotations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%spaces_annotations}}',
            'enable_facet',
            $this->boolean()->notNull()->defaultValue(1)->after('enabled')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%spaces_annotations}}', 'enable_facet');
    }
}
