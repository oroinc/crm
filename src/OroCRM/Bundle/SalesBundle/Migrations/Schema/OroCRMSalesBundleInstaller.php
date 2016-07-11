<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema;

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
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;

use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_5\OroCRMSalesBundle as SalesNoteMigration;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_7\OpportunityAttachment;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_11\OroCRMSalesBundle as SalesOrganizations;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_21\InheritanceActivityTargets;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24\InheritanceActivityTargets as OpportunityLeadInheritance;
use OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22\AddOpportunityStatus;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMSalesBundleInstaller implements
    Installation,
    ExtendExtensionAwareInterface,
    NoteExtensionAwareInterface,
    ActivityExtensionAwareInterface,
    AttachmentExtensionAwareInterface,
    ActivityListExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var NoteExtension */
    protected $noteExtension;

    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var AttachmentExtension */
    protected $attachmentExtension;

    /** @var ActivityListExtension */
    protected $activityListExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityListExtension(ActivityListExtension $activityListExtension)
    {
        $this->activityListExtension = $activityListExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setNoteExtension(NoteExtension $noteExtension)
    {
        $this->noteExtension = $noteExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttachmentExtension(AttachmentExtension $attachmentExtension)
    {
        $this->attachmentExtension = $attachmentExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_24';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmSalesOpportunityTable($schema);
        $this->createOrocrmSalesLeadStatusTable($schema);
        $this->createOrocrmSalesFunnelTable($schema);
        $this->createOrocrmSalesOpportStatusTable($schema);
        $this->createOrocrmSalesOpportCloseRsnTable($schema);
        $this->createOrocrmSalesLeadTable($schema);
        $this->createOrocrmSalesB2bCustomerTable($schema);

        /** Tables update */
        $this->addOroEmailMailboxProcessorColumns($schema);

        /** Foreign keys generation **/
        $this->addOrocrmSalesOpportunityForeignKeys($schema);
        $this->addOrocrmSalesFunnelForeignKeys($schema);
        $this->addOrocrmSalesLeadForeignKeys($schema);
        $this->addOrocrmSalesB2bCustomerForeignKeys($schema);
        $this->addOroEmailMailboxProcessorForeignKeys($schema);

        /** Apply extensions */
        SalesNoteMigration::addNoteAssociations($schema, $this->noteExtension);
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_sales_b2bcustomer');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_sales_b2bcustomer');
        $this->activityExtension->addActivityAssociation($schema, 'oro_calendar_event', 'orocrm_sales_lead');
        $this->activityExtension->addActivityAssociation($schema, 'oro_calendar_event', 'orocrm_sales_opportunity');
        $this->activityExtension->addActivityAssociation($schema, 'oro_calendar_event', 'orocrm_sales_b2bcustomer');
        OpportunityAttachment::addOpportunityAttachment($schema, $this->attachmentExtension);
        InheritanceActivityTargets::addInheritanceTargets($schema, $this->activityListExtension);
        OpportunityLeadInheritance::addInheritanceTargets($schema, $this->activityListExtension);

        SalesOrganizations::addOrganization($schema);
        AddOpportunityStatus::addStatusField($schema, $this->extendExtension, $queries);
    }

    /**
     * Create orocrm_sales_opportunity table
     *
     * @param Schema $schema
     */
    protected function createOrocrmSalesOpportunityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_opportunity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('close_reason_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('close_date', 'date', ['notnull' => false]);
        $table->addColumn(
            'probability',
            'percent',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:percent)']
        );
        $table->addColumn(
            'budget_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'close_revenue',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('customer_need', 'text', ['notnull' => false]);
        $table->addColumn('proposed_solution', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addIndex(['contact_id'], 'idx_c0fe4aace7a1254a', []);
        $table->addIndex(['created_at'], 'opportunity_created_idx', []);
        $table->addIndex(['user_owner_id'], 'idx_c0fe4aac9eb185f9', []);
        $table->addIndex(['lead_id'], 'idx_c0fe4aac55458d', []);
        $table->addIndex(['customer_id'], 'IDX_C0FE4AAC9395C3F3', []);
        $table->addIndex(['data_channel_id'], 'IDX_C0FE4AACBDC09B73', []);
        $table->addIndex(['close_reason_name'], 'idx_c0fe4aacd81b931c', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_sales_lead_status table
     *
     * @param Schema $schema
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
     * Create orocrm_sales_funnel table
     *
     * @param Schema $schema
     */
    protected function createOrocrmSalesFunnelTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_funnel');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('lead_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('startdate', 'date', []);
        $table->addColumn('createdat', 'datetime', []);
        $table->addColumn('updatedat', 'datetime', ['notnull' => false]);
        $table->addIndex(['opportunity_id'], 'idx_e20c73449a34590f', []);
        $table->addIndex(['lead_id'], 'idx_e20c734455458d', []);
        $table->addIndex(['startdate'], 'sales_start_idx', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_owner_id'], 'idx_e20c73449eb185f9', []);
        $table->addIndex(['data_channel_id'], 'IDX_E20C7344BDC09B73', []);
    }

    /**
     * Create orocrm_sales_opport_status table
     *
     * @param Schema $schema
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
     * Create orocrm_sales_opport_close_rsn table
     *
     * @param Schema $schema
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
     * Create orocrm_sales_lead table
     *
     * @param Schema $schema
     */
    protected function createOrocrmSalesLeadTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_lead');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('address_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('job_title', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('phone_number', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('company_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('website', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('number_of_employees', 'integer', ['notnull' => false]);
        $table->addColumn('industry', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('createdat', 'datetime', []);
        $table->addColumn('updatedat', 'datetime', ['notnull' => false]);
        $table->addColumn('notes', 'text', ['notnull' => false]);

        $this->extendExtension->addEnumField(
            $schema,
            'orocrm_sales_lead',
            'source',
            'lead_source'
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $table,
            'campaign',
            'orocrm_campaign',
            'combined_name',
            ['extend' => ['owner' => ExtendScope::OWNER_CUSTOM]]
        );

        $table->addIndex(['status_name'], 'idx_73db46336625d392', []);
        $table->addIndex(['user_owner_id'], 'idx_73db46339eb185f9', []);
        $table->addIndex(['customer_id'], 'IDX_73DB46339395C3F3', []);
        $table->addIndex(['data_channel_id'], 'IDX_73DB4633BDC09B73', []);
        $table->addIndex(['createdat'], 'lead_created_idx', []);
        $table->addIndex(['contact_id'], 'idx_73db4633e7a1254a', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['address_id'], 'idx_73db4633f5b7af75', []);
    }

    /**
     * Create orocrm_sales_b2bcustomer table
     *
     * @param Schema $schema
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
     * Add orocrm_sales_opportunity foreign keys.
     *
     * @param Schema $schema
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
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_C0FE4AACBDC09B73'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead'),
            ['lead_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_sales_funnel foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmSalesFunnelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_funnel');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_opportunity'),
            ['opportunity_id'],
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
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_E20C7344BDC09B73'
        );
    }

    /**
     * Add orocrm_sales_lead foreign keys.
     *
     * @param Schema $schema
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
            $schema->getTable('oro_address'),
            ['address_id'],
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
            $schema->getTable('orocrm_sales_b2bcustomer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_73DB4633BDC09B73'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_lead_status'),
            ['status_name'],
            ['name'],
            ['onUpdate' => null, 'onDelete' => null]
        );
    }

    /**
     * Add orocrm_sales_b2bcustomer foreign keys.
     *
     * @param Schema $schema
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
     * Create oro_email_mailbox_processor table
     *
     * @param Schema $schema
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
     *
     * @param Schema $schema
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
}
