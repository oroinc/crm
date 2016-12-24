<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;

class OroChannelBundle implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

     /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmChannelTable($schema);
        $this->createOrocrmChannelEntityNameTable($schema);
        $this->createOrocrmChannelCustIdentityTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmChannelForeignKeys($schema);
        $this->addOrocrmChannelEntityNameForeignKeys($schema);
        $this->addOrocrmChannelCustIdentityForeignKeys($schema);

        /** Add extended fields */
        $this->addExtendedFields($schema);
    }

    /**
     * Create oro_channel table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('data_source_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('status', 'boolean', []);
        $table->addColumn('channel_type', 'string', ['length' => 255]);
        $table->addColumn('customer_identity', 'string', ['length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['data_source_id'], 'UNIQ_AEA90B921A935C57');
        $table->addIndex(['organization_owner_id'], 'IDX_AEA90B929124A35B', []);
    }

    /**
     * Create oro_channel_entity_name table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelEntityNameTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_entity_name');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['channel_id'], 'IDX_92BC967172F5A1AA', []);
    }

    /**
     * Create oro_channel_cust_identity table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelCustIdentityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_cust_identity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
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
        $table->addIndex(['data_channel_id'], 'IDX_30F8588572F5A1AA', []);
    }

    /**
     * Add oro_channel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['data_source_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_AEA90B921A935C57'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_AEA90B929124A35B'
        );
    }

    /**
     * Add oro_channel_entity_name foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelEntityNameForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_entity_name');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_92BC967172F5A1AA'
        );
    }

    /**
     * Add oro_channel_cust_identity foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelCustIdentityForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_cust_identity');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
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

    /**
     * @param $schema
     */
    public function addExtendedFields($schema)
    {
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_embedded_form',
            'dataChannel',
            'orocrm_channel',
            'name',
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM, 'is_extend' => true],
                'form' => ['is_enabled' => false]
            ]
        );
    }
}
