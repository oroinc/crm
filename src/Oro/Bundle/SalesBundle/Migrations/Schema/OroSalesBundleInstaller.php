<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareTrait;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareTrait;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareTrait;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;

/**
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
    use ExtendExtensionAwareTrait;
    use ActivityExtensionAwareTrait;
    use AttachmentExtensionAwareTrait;
    use ActivityListExtensionAwareTrait;
    use RenameExtensionAwareTrait;
    use CustomerExtensionTrait;

    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_44';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createOrocrmSalesOpportunityTable($schema);
        $this->createOrocrmSalesOpportunityCloseRsnTable($schema);
        $this->createOrocrmSalesLeadTable($schema);
        $this->createOrocrmSalesB2bCustomerTable($schema);
        $this->createOrocrmLeadPhoneTable($schema);
        $this->createOrocrmSalesLeadEmailTable($schema, $queries);
        $this->createOrocrmB2bCustomerPhoneTable($schema);
        $this->createOrocrmB2bCustomerEmailTable($schema);
        $this->createOrocrmCustomerTable($schema);
        $this->createOrocrmLeadAddressTable($schema);

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
        $this->addOrocrmCustomerTableForeignKeys($schema);
        $this->createOrocrmLeadAddressTableForeignKeys($schema);

        $this->addAttachmentAssociations($schema);
        $this->addActivityAssociations($schema);
        $this->addActivityListAssociations($schema);
        $this->addOpportunityStatusField($schema, $queries);
        $this->addLeadStatusField($schema, $queries);
        $this->customerExtension->addCustomerAssociation($schema, 'orocrm_sales_b2bcustomer');

        $this->addLeadOwnerToOroEmailAddress($schema);
    }

    /**
     * Create oro_sales_opportunity table
     */
    private function createOrocrmSalesOpportunityTable(Schema $schema): void
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
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addColumn('closed_at', 'datetime', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['contact_id'], 'idx_c0fe4aace7a1254a');
        $table->addIndex(['created_at', 'id'], 'opportunity_created_idx');
        $table->addIndex(['user_owner_id'], 'idx_c0fe4aac9eb185f9');
        $table->addIndex(['lead_id'], 'idx_c0fe4aac55458d');
        $table->addIndex(['close_reason_name'], 'idx_c0fe4aacd81b931c');
        $table->addIndex(['customer_association_id'], 'IDX_C0FE4AAC76D4FC6F');
        $table->addIndex(['organization_id'], 'IDX_C0FE4AAC32C8A3DE');
    }

    /**
     * Create oro_sales_opport_close_rsn table
     */
    private function createOrocrmSalesOpportunityCloseRsnTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_sales_opport_close_rsn');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'uniq_fa526a41ea750e8');
    }

    /**
     * Create oro_sales_lead table
     */
    private function createOrocrmSalesLeadTable(Schema $schema): void
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
        $table->addColumn('createdat', 'datetime');
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
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_owner_id'], 'idx_73db46339eb185f9');
        $table->addIndex(['createdat', 'id'], 'lead_created_idx');
        $table->addIndex(['updatedat'], 'lead_updated_idx');
        $table->addIndex(['contact_id'], 'idx_73db4633e7a1254a');
        $table->addIndex(['customer_association_id'], 'IDX_73DB463376D4FC6F');
        $table->addIndex(['organization_id'], 'IDX_73DB463332C8A3DE');
    }

    /**
     * Create oro_sales_b2bcustomer table
     */
    private function createOrocrmSalesB2bCustomerTable(Schema $schema): void
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
        $table->addColumn('lifetime', 'money', [
            'notnull' => false,
            OroOptions::KEY => [
                'dataaudit' => ['auditable' => false],
            ]
        ]);
        $table->addColumn('createdAt', 'datetime');
        $table->addColumn('updatedAt', 'datetime');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['account_id'], 'IDX_94CC12929B6B5FBA');
        $table->addIndex(['shipping_address_id'], 'IDX_9C6CFD74D4CFF2B');
        $table->addIndex(['billing_address_id'], 'IDX_9C6CFD779D0C0E4');
        $table->addIndex(['contact_id'], 'IDX_9C6CFD7E7A1254A');
        $table->addIndex(['data_channel_id'], 'IDX_DAC0BD29BDC09B73');
        $table->addIndex(['user_owner_id'], 'IDX_9C6CFD79EB185F9');
        $table->addIndex(['name', 'id'], 'orocrm_b2bcustomer_name_idx');
        $table->addIndex(['organization_id'], 'idx_dac0bd2932c8a3de');

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
    private function createOrocrmLeadPhoneTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_sales_lead_phone');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_8475907F7E3C61F9');
        $table->addIndex(['phone', 'is_primary'], 'lead_primary_phone_idx');
        $table->addIndex(['phone'], 'lead_phone_idx');
    }

    /**
     * Create oro_sales_lead_email table
     */
    private function createOrocrmSalesLeadEmailTable(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->createTable('orocrm_sales_lead_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_9F15A0937E3C61F9');
        $table->addIndex(['email', 'is_primary'], 'lead_primary_email_idx');

        if ($this->platform instanceof PostgreSqlPlatform) {
            $queries->addPostQuery(new SqlMigrationQuery(
                'CREATE INDEX idx_lead_email_ci ON orocrm_sales_lead_email (LOWER(email))'
            ));
        }
    }

    /**
     * Create oro_b2bcustomer_phone table
     */
    private function createOrocrmB2bCustomerPhoneTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_sales_b2bcustomer_phone');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_F0D0BDFA7E3C61F9');
        $table->addIndex(['phone', 'is_primary'], 'primary_b2bcustomer_phone_idx');
        $table->addIndex(['phone'], 'b2bcustomer_phone_idx');
    }

    /**
     * Create oro_b2bcustomer_email table
     */
    private function createOrocrmB2bCustomerEmailTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_sales_b2bcustomer_email');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_D564AB17E3C61F9');
        $table->addIndex(['email', 'is_primary'], 'primary_b2bcustomer_email_idx');
    }

    /**
     * Create orocrm_sales_customer table
     */
    private function createOrocrmCustomerTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_sales_customer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('account_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_sales_lead_address table
     */
    private function createOrocrmLeadAddressTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_sales_lead_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created', 'datetime');
        $table->addColumn('updated', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_LEAD_ADDRESS_OWNER');
        $table->addIndex(['country_code'], 'IDX_LEAD_ADDRESS_COUNTRY');
        $table->addIndex(['region_code'], 'IDX_LEAD_ADDRESS_REGION');
    }

    /**
     * Add oro_sales_opportunity foreign keys.
     */
    private function addOrocrmSalesOpportunityForeignKeys(Schema $schema): void
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
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_sales_lead foreign keys.
     */
    private function addOrocrmSalesLeadForeignKeys(Schema $schema): void
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
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_sales_b2bcustomer foreign keys.
     */
    private function addOrocrmSalesB2bCustomerForeignKeys(Schema $schema): void
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
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    private function addAttachmentAssociations(Schema $schema): void
    {
        $this->attachmentExtension->addAttachmentAssociation(
            $schema,
            'orocrm_sales_opportunity',
            [
                'image/*',
                'application/pdf',
                'application/zip',
                'application/x-gzip',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ],
            2
        );
    }

    private function addActivityAssociations(Schema $schema): void
    {
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_b2bcustomer');
    }

    private function addActivityListAssociations(Schema $schema): void
    {
        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_lead',
            ['contact', 'accounts']
        );
        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_opportunity',
            ['customerAssociation', 'account']
        );
        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_account',
            'orocrm_sales_b2bcustomer',
            ['account']
        );
        $this->activityListExtension->addInheritanceTargets(
            $schema,
            'orocrm_sales_opportunity',
            'orocrm_sales_lead',
            ['opportunities']
        );
    }

    private function addOpportunityStatusField(Schema $schema, QueryBag $queries): void
    {
        $this->extendExtension->addEnumField(
            $schema,
            'orocrm_sales_opportunity',
            'status',
            'opportunity_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'dataaudit' => ['auditable' => true],
                'importexport' => ['order' => 90, 'short' => true]
            ]
        );

        $enumOptionIds = [
            ExtendHelper::buildEnumOptionId('opportunity_status', 'in_progress'),
            ExtendHelper::buildEnumOptionId('opportunity_status', 'won'),
            ExtendHelper::buildEnumOptionId('opportunity_status', 'lost'),
        ];
        $schema->getTable('orocrm_sales_opportunity')
            ->addExtendColumnOption(
                'status',
                'enum',
                'immutable_codes',
                $enumOptionIds
            );
    }

    private function addLeadStatusField(Schema $schema, QueryBag $queries): void
    {
        $this->extendExtension->addEnumField(
            $schema,
            'orocrm_sales_lead',
            'status',
            'lead_status',
            false,
            false,
            [
               'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
               'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
               'dataaudit' => ['auditable' => true],
               'importexport' => ['order' => 90, 'short' => true]
            ]
        );
        $enumOptionIds = [
            ExtendHelper::buildEnumOptionId('lead_status', 'new'),
            ExtendHelper::buildEnumOptionId('lead_status', 'qualified'),
            ExtendHelper::buildEnumOptionId('lead_status', 'canceled'),
        ];
        $schema->getTable('orocrm_sales_lead')
            ->addExtendColumnOption(
                'status',
                'enum',
                'immutable_codes',
                $enumOptionIds
            );
    }

    /**
     * Add lead related columns to oro_email_mailbox_process table.
     */
    private function addOroEmailMailboxProcessorColumns(Schema $schema): void
    {
        $table = $schema->getTable('oro_email_mailbox_process');
        $table->addColumn('lead_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_source_id', 'string', ['notnull' => false, 'length' => 32]);
        $table->addIndex(['lead_owner_id'], 'IDX_CE8602A3D46FE3FA');
        $table->addIndex(['lead_channel_id'], 'IDX_CE8602A35A6EBA36');
    }

    /**
     * Add oro_email_mailbox_processor foreign keys.
     */
    private function addOroEmailMailboxProcessorForeignKeys(Schema $schema): void
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
    private function addOrocrmB2bCustomerPhoneForeignKeys(Schema $schema): void
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
    private function addOrocrmB2bCustomerEmailForeignKeys(Schema $schema): void
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
    private function addOrocrmLeadPhoneForeignKeys(Schema $schema): void
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
    private function addOrocrmSalesLeadEmailForeignKeys(Schema $schema): void
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
     * Add orocrm_sales_customer foreign keys.
     */
    private function addOrocrmCustomerTableForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_sales_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add orocrm_sales_lead_address foreign keys.
     */
    private function createOrocrmLeadAddressTableForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_sales_lead_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
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
