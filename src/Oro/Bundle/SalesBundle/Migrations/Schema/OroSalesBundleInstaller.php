<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtension;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_11\OroSalesBundle as SalesOrganizations;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_21\InheritanceActivityTargets;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_22\AddOpportunityStatus;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_24\AddLeadStatus;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_24\InheritanceActivityTargets as OpportunityLeadInheritance;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_25\AddLeadAddressTable;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_29\AddCustomersTable;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_7\OpportunityAttachment;

/**
 * Updates the database schema during installation.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroSalesBundleInstaller implements
    Installation,
    DatabasePlatformAwareInterface,
    ExtendExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    AttachmentExtensionAwareInterface,
    ActivityListExtensionAwareInterface,
    RenameExtensionAwareInterface,
    CustomerExtensionAwareInterface
{
    use DatabasePlatformAwareTrait;
    use CustomerExtensionTrait;

    protected ExtendExtension $extendExtension;
    protected ActivityExtension $activityExtension;
    protected AttachmentExtension $attachmentExtension;
    protected ActivityListExtension $activityListExtension;
    protected RenameExtension $renameExtension;

    /**
     * {@inheritDoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension): void
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }

    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_42';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmSalesOpportunityTable($schema);
        $this->createOrocrmSalesOpportCloseRsnTable($schema);
        $this->createOrocrmSalesLeadTable($schema);
        $this->createOrocrmSalesB2bCustomerTable($schema);
        $this->createOrocrmLeadPhoneTable($schema);
        $this->createOrocrmSalesLeadEmailTable($schema, $queries);
        $this->createOrocrmB2bCustomerPhoneTable($schema);
        $this->createOrocrmB2bCustomerEmailTable($schema);
        AddCustomersTable::addCustomersTable($schema);
        $this->addB2bCustomerNameIndex($schema);

        /** Tables update */
        $this->addOroEmailMailboxProcessorColumns($schema);

        /** Foreign keys generation **/
        $this->addOrocrmSalesOpportunityForeignKeys($schema);
        $this->addOrocrmSalesLeadForeignKeys($schema);
        $this->addOrocrmSalesB2bCustomerForeignKeys($schema);
        $this->addOroEmailMailboxProcessorForeignKeys($schema);
        $this->addOrocrmB2bCustomerPhoneForeignKeys($schema);
        $this->addOrocrmB2bCustomerEmailForeignKeys($schema);
        $this->addOrocrmLeadPhoneForeignKeys($schema);
        $this->addOrocrmSalesLeadEmailForeignKeys($schema);
        AddCustomersTable::addCustomersTableForeignKeys($schema);

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_b2bcustomer');
        OpportunityAttachment::addOpportunityAttachment($schema, $this->attachmentExtension);
        InheritanceActivityTargets::addInheritanceTargets($schema, $this->activityListExtension);
        OpportunityLeadInheritance::addInheritanceTargets($schema, $this->activityListExtension);

        SalesOrganizations::addOrganization($schema);

        $this->addOpportunityStatusField($schema, $queries);
        AddLeadStatus::addStatusField($schema, $this->extendExtension, $queries);
        AddLeadAddressTable::createLeadAddressTable($schema);
        $this->customerExtension->addCustomerAssociation($schema, 'orocrm_sales_b2bcustomer');

        $this->addOpportunitiesByStatusIndex($schema);
        $this->addLeadOwnerToOroEmailAddress($schema);
    }

    /**
     * Create oro_sales_opportunity table
     */
    protected function createOrocrmSalesOpportunityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_opportunity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('close_reason_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('close_date', 'date', ['notnull' => false]);
        $table->addColumn(
            'probability',
            'percent',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:percent)']
        );
        $table->addColumn(
            'budget_amount_value',
            'money_value',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money_value)']
        );
        $table->addColumn(
            'budget_amount_currency',
            'currency',
            ['length' => 3, 'notnull' => false, 'comment' => '(DC2Type:currency)']
        );
        $table->addColumn(
            'base_budget_amount_value',
            'money',
            ['notnull' => false, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'close_revenue_value',
            'money_value',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money_value)']
        );
        $table->addColumn(
            'close_revenue_currency',
            'currency',
            ['length' => 3, 'notnull' => false, 'comment' => '(DC2Type:currency)']
        );
        $table->addColumn('customer_association_id', 'integer', ['notnull' => false]);

        $table->addColumn(
            'base_close_revenue_value',
            'money',
            ['notnull' => false, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('customer_need', 'text', ['notnull' => false]);
        $table->addColumn('proposed_solution', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addColumn('closed_at', 'datetime', ['notnull' => false]);
        $table->addIndex(['contact_id'], 'idx_c0fe4aace7a1254a', []);
        $table->addIndex(['created_at', 'id'], 'opportunity_created_idx', []);
        $table->addIndex(['user_owner_id'], 'idx_c0fe4aac9eb185f9', []);
        $table->addIndex(['lead_id'], 'idx_c0fe4aac55458d', []);
        $table->addIndex(['close_reason_name'], 'idx_c0fe4aacd81b931c', []);
        $table->addIndex(['customer_association_id'], 'IDX_C0FE4AAC76D4FC6F', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create oro_sales_lead_status table
     */
    protected function createOrocrmSalesLeadStatusTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_lead_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addUniqueIndex(['label'], 'uniq_4516951bea750e8');
        $table->setPrimaryKey(['name']);
    }

    /**
     * Create oro_sales_opport_status table
     */
    protected function createOrocrmSalesOpportStatusTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_opport_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addUniqueIndex(['label'], 'uniq_2db212b5ea750e8');
        $table->setPrimaryKey(['name']);
    }

    /**
     * Create oro_sales_opport_close_rsn table
     */
    protected function createOrocrmSalesOpportCloseRsnTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_opport_close_rsn');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->addUniqueIndex(['label'], 'uniq_fa526a41ea750e8');
        $table->setPrimaryKey(['name']);
    }

    /**
     * Create oro_sales_lead table
     */
    protected function createOrocrmSalesLeadTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_lead');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('job_title', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('company_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('website', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('number_of_employees', 'integer', ['notnull' => false]);
        $table->addColumn('industry', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdat', 'datetime', []);
        $table->addColumn('updatedat', 'datetime', ['notnull' => false]);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addColumn('twitter', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('linkedin', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('customer_association_id', 'integer', ['notnull' => false]);

        $this->extendExtension->addEnumField(
            $schema,
            'orocrm_sales_lead',
            'source',
            'lead_source'
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            'orocrm_sales_lead',
            'campaign',
            'orocrm_campaign',
            'combined_name',
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
                'datagrid' => [
                    'is_visible' => DatagridScope::IS_VISIBLE_FALSE,
                ],
                'merge' => ['inverse_display' => false],
            ]
        );

        $table->addIndex(['user_owner_id'], 'idx_73db46339eb185f9', []);
        $table->addIndex(['createdat', 'id'], 'lead_created_idx', []);
        $table->addIndex(['updatedat'], 'lead_updated_idx');
        $table->addIndex(['contact_id'], 'idx_73db4633e7a1254a', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['customer_association_id'], 'IDX_73DB463376D4FC6F', []);
    }

    /**
     * Create oro_sales_b2bcustomer table
     */
    protected function createOrocrmSalesB2bCustomerTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_b2bcustomer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('lifetime', 'money', ['notnull' => false]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['account_id'], 'IDX_94CC12929B6B5FBA', []);
        $table->addIndex(['shipping_address_id'], 'IDX_9C6CFD74D4CFF2B', []);
        $table->addIndex(['billing_address_id'], 'IDX_9C6CFD779D0C0E4', []);
        $table->addIndex(['contact_id'], 'IDX_9C6CFD7E7A1254A', []);
        $table->addIndex(['data_channel_id'], 'IDX_DAC0BD29BDC09B73', []);
        $table->addIndex(['user_owner_id'], 'IDX_9C6CFD79EB185F9', []);

        $table->addColumn(
            'website',
            'string',
            [
                'oro_options' => [
                    'extend'    => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'employees',
            'integer',
            [
                'oro_options' => [
                    'extend'    => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'ownership',
            'string',
            [
                'oro_options' => [
                    'extend'    => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'ticker_symbol',
            'string',
            [
                'oro_options' => [
                    'extend'    => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
        $table->addColumn(
            'rating',
            'string',
            [
                'oro_options' => [
                    'extend'    => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_HIDDEN],
                    'dataaudit' => ['auditable' => true]
                ]
            ]
        );
    }

    /**
     * Create oro_lead_phone table
     */
    protected function createOrocrmLeadPhoneTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_lead_phone');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_8475907F7E3C61F9', []);
        $table->addIndex(['phone', 'is_primary'], 'lead_primary_phone_idx', []);
        $table->addIndex(['phone'], 'lead_phone_idx');
    }

    /**
     * Create oro_sales_lead_email table
     */
    protected function createOrocrmSalesLeadEmailTable(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('orocrm_sales_lead_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_9F15A0937E3C61F9', []);
        $table->addIndex(['email', 'is_primary'], 'lead_primary_email_idx', []);

        if ($this->platform instanceof PostgreSqlPlatform) {
            $queries->addPostQuery(new SqlMigrationQuery(
                'CREATE INDEX idx_lead_email_ci ON orocrm_sales_lead_email (LOWER(email))'
            ));
        }
    }

    /**
     * Create oro_b2bcustomer_phone table
     */
    protected function createOrocrmB2bCustomerPhoneTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_b2bcustomer_phone');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_F0D0BDFA7E3C61F9', []);
        $table->addIndex(['phone', 'is_primary'], 'primary_b2bcustomer_phone_idx', []);
        $table->addIndex(['phone'], 'b2bcustomer_phone_idx');
    }

    /**
     * Create oro_b2bcustomer_email table
     */
    protected function createOrocrmB2bCustomerEmailTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_b2bcustomer_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_D564AB17E3C61F9', []);
        $table->addIndex(['email', 'is_primary'], 'primary_b2bcustomer_email_idx', []);
    }

    /**
     * Add oro_sales_opportunity foreign keys.
     */
    protected function addOrocrmSalesOpportunityForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_opport_close_rsn'),
            ['close_reason_name'],
            ['name'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['lead_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['customer_association_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_sales_lead foreign keys.
     */
    protected function addOrocrmSalesLeadForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_campaign'),
            ['campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['customer_association_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_sales_b2bcustomer foreign keys.
     */
    protected function addOrocrmSalesB2bCustomerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_b2bcustomer');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address'),
            ['shipping_address_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address'),
            ['billing_address_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add opportunity status Enum field and initialize default enum values
     */
    protected function addOpportunityStatusField(Schema $schema, QueryBag $queries)
    {
        $immutableCodes = ['in_progress', 'won', 'lost'];

        AddOpportunityStatus::addStatusField($schema, $this->extendExtension, $immutableCodes);

        $statuses = [
            'in_progress' => 'Open',
            'identification_alignment' => 'Identification & Alignment',
            'needs_analysis' => 'Needs Analysis',
            'solution_development' => 'Solution Development',
            'negotiation' => 'Negotiation',
            'won' => 'Closed Won',
            'lost' => 'Closed Lost',
        ];

        AddOpportunityStatus::addEnumValues($queries, $statuses);
    }

    /**
     * Create oro_email_mailbox_processor table
     */
    public static function addOroEmailMailboxProcessorColumns(Schema $schema)
    {
        $table = $schema->getTable('oro_email_mailbox_process');

        $table->addColumn('lead_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_source_id', 'string', ['notnull' => false, 'length' => 32]);
        $table->addIndex(['lead_owner_id'], 'IDX_CE8602A3D46FE3FA', []);
        $table->addIndex(['lead_channel_id'], 'IDX_CE8602A35A6EBA36', []);
    }

    /**
     * Add oro_email_mailbox_processor foreign keys.
     */
    public static function addOroEmailMailboxProcessorForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_email_mailbox_process');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['lead_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['lead_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_b2bcustomer_phone foreign keys.
     */
    protected function addOrocrmB2bCustomerPhoneForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_b2bcustomer_phone');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_b2bcustomer_email foreign keys.
     */
    protected function addOrocrmB2bCustomerEmailForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_b2bcustomer_email');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_lead_phone foreign keys.
     */
    protected function addOrocrmLeadPhoneForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead_phone');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_sales_lead_email foreign keys.
     */
    protected function addOrocrmSalesLeadEmailForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_lead_email');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add orocrm_sales_b2bcustomer index on field name
     */
    protected function addB2bCustomerNameIndex(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_b2bcustomer');
        $table->addIndex(['name', 'id'], 'orocrm_b2bcustomer_name_idx', []);
    }

    /**
     * Add opportunity 'opportunities_by_status_idx' index, used to speedup 'Opportunity By Status' widget
     */
    protected function addOpportunitiesByStatusIndex(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->addIndex(
            ['organization_id', 'status_id', 'close_revenue_value', 'budget_amount_value', 'created_at'],
            'opportunities_by_status_idx'
        );
    }

    private function addLeadOwnerToOroEmailAddress(Schema $schema): void
    {
        $table = $schema->getTable('oro_email_address');
        $table->addColumn('owner_lead_id', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_lead_id']);
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['owner_lead_id'],
            ['id']
        );
    }
}
