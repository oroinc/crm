<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCaseBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addOroEmailMailboxProcessSettingsColumns($schema);
        self::addOroEmailMailboxProcessSettingsForeignKeys($schema);
    }

    /**
     * Adds required columns to oro_email_mailbox_process table.
     */
    public static function addOroEmailMailboxProcessSettingsColumns(Schema $schema)
    {
        $table = $schema->getTable('oro_email_mailbox_process');

        $table->addColumn('case_assign_to_id', 'integer', ['notnull' => false]);
        $table->addColumn('case_status_name', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('case_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('case_priority_name', 'string', ['notnull' => false, 'length' => 16]);

        $table->addIndex(['case_owner_id'], 'IDX_CE8602A3E9411B84', []);
        $table->addIndex(['case_assign_to_id'], 'IDX_CE8602A37CFDD645', []);
        $table->addIndex(['case_priority_name'], 'IDX_CE8602A3F1B25087', []);
        $table->addIndex(['case_status_name'], 'IDX_CE8602A3C168B4FB', []);
    }

    /**
     * Adds foreign keys to new columns in oro_email_mailbox_process table.
     */
    public static function addOroEmailMailboxProcessSettingsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_email_mailbox_process');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['case_assign_to_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_status'),
            ['case_status_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['case_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_case_priority'),
            ['case_priority_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
