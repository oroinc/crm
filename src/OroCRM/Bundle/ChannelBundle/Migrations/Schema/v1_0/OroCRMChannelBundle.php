<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMChannelBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('orocrm_channel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('organization_owner_id', 'integer', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_owner_id'], 'IDX_AEA90B929124A35B', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_AEA90B929124A35B'
        );

        $table = $schema->createTable('orocrm_channel_entity_name');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['channel_id'], 'IDX_92BC967172F5A1AA', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_92BC967172F5A1AA'
        );

        self::addChannelIntegrationTable($schema);

        self::addChannelIntegrationTableForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    protected static function addChannelIntegrationTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_integrations');
        $table->addColumn('channel_id', 'integer', ['notnull' => true]);
        $table->addColumn('integrations_id', 'smallint', ['notnull' => true]);
        $table->setPrimaryKey(['channel_id', 'integrations_id']);
        $table->addIndex(['channel_id'], 'IDX_1E77222472F5A1AA', []);
        $table->addIndex(['integrations_id'], 'IDX_1E772224A730349E', []);
    }

    /**
     * @param Schema $schema
     */
    protected static function addChannelIntegrationTableForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_integrations');

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]/*,
            'FK_1E77222472F5A1AA'*/
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['integrations_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]/*,
            'FK_1E772224A730349E'*/
        );
    }
}
