<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_52;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddMagentoOrderNotes implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createOrocrmMagentoOrderNotesTable($schema);
        $this->addOrocrmMagentoOrderNotesForeignKeys($schema);
    }

    /**
     * Add oro_magento_order_notes table.
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoOrderNotesTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_order_notes');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn(
            'origin_id',
            'integer',
            [
                'notnull' => false,
                'precision' => 0,
                'unsigned' => true
            ]
        );
        $table->addColumn('message', 'text');
        $table->addColumn('created_at', 'datetime', ['precision' => 0]);
        $table->addColumn('updated_at', 'datetime', ['precision' => 0]);
        $table->addColumn(
            'channel_id',
            'integer',
            [
                'notnull' => false,
                'precision' => 0,
                'unsigned' => false
            ]
        );
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);

        $table->addIndex(['order_id'], 'IDX_D130A0378D9F6D38', []);
        $table->addIndex(['origin_id'], 'IDX_D130A03756A273CC', []);
        $table->addIndex(['channel_id'], 'IDX_D130A03772F5A1AA', []);
        $table->addIndex(['user_owner_id'], 'IDX_D130A0379EB185F9', []);
        $table->addIndex(['organization_id'], 'IDX_D130A03732C8A3DE', []);

        $table->addUniqueIndex(['origin_id', 'channel_id'], 'unq_order_note_origin_id_channel_id');

        $table->setPrimaryKey(['id']);
    }

    /**
     * Add oro_magento_order_notes foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoOrderNotesForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order_notes');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'FK_D130A0378D9F6D38'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_D130A03772F5A1AA'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL'],
            'FK_D130A0379EB185F9'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_D130A03732C8A3DE'
        );
    }
}
