<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMChannelBundleInstaller implements Installation, ExtendExtensionAwareInterface
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
    public function getMigrationVersion()
    {
        return 'v1_6';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmChannelTable($schema);
        $this->createOrocrmChannelCustIdentityTable($schema);
        $this->createOrocrmChannelEntityNameTable($schema);
        $this->createOrocrmChannelLifetimeHistTable($schema);
        $this->createOrocrmChannelLtimeAvgAggrTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmChannelForeignKeys($schema);
        $this->addOrocrmChannelCustIdentityForeignKeys($schema);
        $this->addOrocrmChannelEntityNameForeignKeys($schema);
        $this->addOrocrmChannelLifetimeHistForeignKeys($schema);
        $this->addOrocrmChannelLtimeAvgAggrForeignKeys($schema);

        /** Add extended fields */
        $this->addExtendedFields($schema);
        $this->addChannelForeignKeyToTrackingWebsite($schema);
    }

    /**
     * Create orocrm_channel table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_source_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('status', 'boolean', []);
        $table->addColumn('channel_type', 'string', ['length' => 255]);
        $table->addColumn('data', Type::JSON_ARRAY, ['notnull' => false]);
        $table->addColumn('customer_identity', 'string', ['length' => 255]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['data_source_id'], 'UNIQ_AEA90B921A935C57');
        $table->addIndex(['organization_owner_id'], 'IDX_AEA90B929124A35B', []);
        $table->addIndex(['name'], 'crm_channel_name_idx', []);
        $table->addIndex(['status'], 'crm_channel_status_idx', []);
        $table->addIndex(['channel_type'], 'crm_channel_channel_type_idx', []);
    }

    /**
     * Create orocrm_channel_cust_identity table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelCustIdentityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_cust_identity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
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
     * Create orocrm_channel_entity_name table
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
     * Create orocrm_channel_lifetime_hist table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelLifetimeHistTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_lifetime_hist');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('status', 'boolean', []);
        $table->addColumn('amount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['data_channel_id'], 'IDX_2B156554BDC09B73', []);
        $table->addIndex(['account_id'], 'IDX_2B1565549B6B5FBA', []);
        $table->addIndex(['account_id', 'data_channel_id', 'status'], 'orocrm_chl_ltv_hist_idx', []);
        $table->addIndex(['status'], 'orocrm_chl_ltv_hist_status_idx', []);
    }

    /**
     * Create orocrm_channel_ltime_avg_aggr table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelLtimeAvgAggrTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_ltime_avg_aggr');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('amount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('aggregation_date', 'datetime', []);
        $table->addColumn('month', 'smallint', ['unsigned' => true]);
        $table->addColumn('quarter', 'smallint', ['unsigned' => true]);
        $table->addColumn('year', 'smallint', ['unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['data_channel_id'], 'IDX_EBDA8490BDC09B73', []);
    }

    /**
     * Add orocrm_channel foreign keys.
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
     * Add orocrm_channel_cust_identity foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelCustIdentityForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_cust_identity');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_30F85885E7A1254A'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_30F858859B6B5FBA'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_30F858859EB185F9'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_30F85885BDC09B73'
        );
    }

    /**
     * Add orocrm_channel_entity_name foreign keys.
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
     * Add orocrm_channel_lifetime_hist foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelLifetimeHistForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_lifetime_hist');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_2B1565549B6B5FBA'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_2B156554BDC09B73'
        );
    }

    /**
     * Add orocrm_channel_ltime_avg_aggr foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelLtimeAvgAggrForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_ltime_avg_aggr');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_EBDA8490BDC09B73'
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
            ['extend' => ['owner' => ExtendScope::OWNER_CUSTOM, 'is_extend' => true]]
        );
    }

    /**
     * Add 'channel' to oro_tracking_website
     *
     * @param Schema $schema
     */
    protected function addChannelForeignKeyToTrackingWebsite(Schema $schema)
    {
        $this->extendExtension->addManyToOneRelation(
            $schema,
            'oro_tracking_website',
            'channel',
            'orocrm_channel',
            'name',
            [
                ExtendOptionsManager::MODE_OPTION => ConfigModel::MODE_READONLY,
                'entity' => ['label' => 'orocrm.channel.entity_label'],
                'extend' => [
                    'is_extend' => true,
                    'owner'     => ExtendScope::OWNER_CUSTOM
                ],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_FALSE
                ],
                'form' => [
                    'is_enabled' => true,
                    'form_type'  => 'orocrm_channel_select_type',
                    'form_options' => [
                        'tooltip'  => 'orocrm.channel.tracking_website_channel_select.tooltip'
                    ]
                ],
                'view'      => ['is_displayable' => true],
                'merge'     => ['display' => false],
                'dataaudit' => ['auditable' => false]
            ]
        );
    }
}
