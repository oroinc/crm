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
<<<<<<< HEAD
        self::addChannelTable($schema);
        self::addChannelEntityNameTable($schema);
        self::addChannelIdentityTable($schema);
=======
        /** Tables generation **/
        $this->createOrocrmChannelTable($schema);
        $this->createOrocrmChannelEntityNameTable($schema);
        $this->createOrocrmChannelCustIdentityTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmChannelForeignKeys($schema);
        $this->addOrocrmChannelEntityNameForeignKeys($schema);
        $this->addOrocrmChannelEntityNameForeignKeys($schema);
>>>>>>> 86af620... Merge branch 'ticket/CRM-1857' into tmp/merge
    }

    /**
     * @param Schema $schema
     */
    public function addChannelTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 255]);
        $table->addColumn('organization_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_source_id', 'integer', ['notnull' => false]);
        $table->addColumn('status', 'boolean', ['notnull' => true]);
        $table->addColumn('channel_type', 'string', ['notnull' => true]);
        $table->addColumn('customer_identity', 'string', ['notnull' => true]);
        $table->addColumn('createdAt', 'datetime', ['notnull' => true]);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_owner_id'], 'IDX_AEA90B929124A35B', []);
        $table->addUniqueIndex(['data_source_id'], 'UNIQ_AEA90B921A935C57');

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_AEA90B929124A35B'
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['data_source_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_AEA90B921A935C57'
        );
    }

    /**
     * @param Schema $schema
     */
    public function addChannelEntityNameTable(Schema $schema)
    {
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
    }

    /**
<<<<<<< HEAD
=======
     * Create orocrm_channel_cust_identity table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelCustIdentityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_cust_identity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['account_id'], 'IDX_30F858859B6B5FBA', []);
        $table->addIndex(['contact_id'], 'IDX_30F85885E7A1254A', []);
        $table->addIndex(['user_owner_id'], 'IDX_30F858859EB185F9', []);
        $table->addIndex(['channel_id'], 'IDX_30F8588572F5A1AA', []);
    }

    /**
     * Add orocrm_channel foreign keys.
     *
>>>>>>> 86af620... Merge branch 'ticket/CRM-1857' into tmp/merge
     * @param Schema $schema
     */
    public function addChannelIdentityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_identity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('createdAt', 'datetime', ['notnull' => true]);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['account_id'], 'IDX_C793C699B6B5FBA', []);
        $table->addIndex(['contact_id'], 'IDX_C793C69E7A1254A', []);
        $table->addIndex(['channel_id'], 'IDX_C793C6972F5A1AA', []);
        $table->addIndex(['user_owner_id'], 'IDX_C793C699EB185F9', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL'],
            'FK_C793C699B6B5FBA'
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL'],
            'FK_C793C69E7A1254A'
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL'],
            'FK_C793C6972F5A1AA'
        );

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL'],
            'FK_C793C699EB185F9'
        );
    }

    /**
     * Add orocrm_channel_cust_identity foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelCustIdentityForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_cust_identity');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
