<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtension;
use Oro\Bundle\NoteBundle\Migration\Extension\NoteExtensionAwareInterface;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtension;
use Oro\Bundle\ActivityListBundle\Migration\Extension\ActivityListExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtensionAwareInterface;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtensionAwareInterface;

use OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_0\OroCRMMagentoBundle as IntegrationUpdate;
use OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_37\CreateActivityAssociation;
use OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_38\InheritanceActivityTargets;
use OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_40\CreateActivityAssociation as OrderActivityAssociation;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class OroCRMMagentoBundleInstaller implements
    Installation,
    ActivityExtensionAwareInterface,
    IdentifierEventExtensionAwareInterface,
    ExtendExtensionAwareInterface,
    VisitEventAssociationExtensionAwareInterface,
    ActivityListExtensionAwareInterface,
    NoteExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /** @var NoteExtension */
    protected $noteExtension;

    /** @var IdentifierEventExtension */
    protected $identifierEventExtension;

    /** @var ExtendExtension $extendExtension */
    protected $extendExtension;

    /** @var VisitEventAssociationExtension */
    protected $visitExtension;

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
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
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
    public function setIdentifierEventExtension(IdentifierEventExtension $identifierEventExtension)
    {
        $this->identifierEventExtension = $identifierEventExtension;
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
    public function setVisitEventAssociationExtension(VisitEventAssociationExtension $extension)
    {
        $this->visitExtension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_41';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOrocrmMagentoRegionTable($schema);
        $this->createOrocrmMagentoCartAddressTable($schema);
        $this->createOrocrmMagentoOrderTable($schema);
        $this->createOrocrmMagentoOrderCallsTable($schema);
        $this->createOrocrmMagentoOrderEmailsTable($schema);
        $this->createOrocrmMagentoCustomerGroupTable($schema);
        $this->createOrocrmMagentoCustomerTable($schema);
        $this->createOrocrmMagentoCartItemTable($schema);
        $this->createOrocrmMagentoCustomerAddrTable($schema);
        $this->createOrocrmMagentoCustAddrTypeTable($schema);
        $this->createOrocrmMagentoOrderAddressTable($schema);
        $this->createOrocrmMagentoOrderAddrTypeTable($schema);
        $this->createOrocrmMagentoProductTable($schema);
        $this->createOrocrmMagentoProdToWebsiteTable($schema);
        $this->createOrocrmMagentoWebsiteTable($schema);
        $this->createOrocrmMagentoCartTable($schema);
        $this->createOrocrmMagentoCartCallsTable($schema);
        $this->createOrocrmMagentoCartEmailsTable($schema);
        $this->createOrocrmMagentoStoreTable($schema);
        $this->createOrocrmMagentoCartStatusTable($schema);
        $this->createOrocrmMagentoOrderItemsTable($schema);
        $this->createOrocrmMagentoNewslSubscrTable($schema);
        $this->updateIntegrationTransportTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmMagentoCartAddressForeignKeys($schema);
        $this->addOrocrmMagentoOrderForeignKeys($schema);
        $this->addOrocrmMagentoOrderCallsForeignKeys($schema);
        $this->addOrocrmMagentoOrderEmailsForeignKeys($schema);
        $this->addOrocrmMagentoCustomerGroupForeignKeys($schema);
        $this->addOrocrmMagentoCustomerForeignKeys($schema);
        $this->addOrocrmMagentoCartItemForeignKeys($schema);
        $this->addOrocrmMagentoCustomerAddrForeignKeys($schema);
        $this->addOrocrmMagentoCustAddrTypeForeignKeys($schema);
        $this->addOrocrmMagentoOrderAddressForeignKeys($schema);
        $this->addOrocrmMagentoOrderAddrTypeForeignKeys($schema);
        $this->addOrocrmMagentoProductForeignKeys($schema);
        $this->addOrocrmMagentoProdToWebsiteForeignKeys($schema);
        $this->addOrocrmMagentoWebsiteForeignKeys($schema);
        $this->addOrocrmMagentoCartForeignKeys($schema);
        $this->addOrocrmMagentoCartCallsForeignKeys($schema);
        $this->addOrocrmMagentoCartEmailsForeignKeys($schema);
        $this->addOrocrmMagentoStoreForeignKeys($schema);
        $this->addOrocrmMagentoOrderItemsForeignKeys($schema);
        $this->addOrocrmMagentoNewslSubscrForeignKeys($schema);

        $this->addActivityAssociations($schema);
        OrderActivityAssociation::addNoteAssociations($schema, $this->noteExtension);
        $this->addIdentifierEventAssociations($schema);
        InheritanceActivityTargets::addInheritanceTargets($schema, $this->activityListExtension);
    }

    /**
     * Update oro_integration_transport table.
     *
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function updateIntegrationTransportTable(Schema $schema)
    {
        IntegrationUpdate::updateOroIntegrationTransportTable($schema);
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('admin_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('initial_sync_start_date', 'datetime', ['notnull' => false]);
        $table->addColumn('extension_version', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('magento_version', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('guest_customer_sync', 'boolean', ['notnull' => false]);
        $table->addColumn('mage_newsl_subscr_synced_to_id', 'integer', ['notnull' => false]);
    }

    /**
     * Create orocrm_magento_region table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoRegionTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_region');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('combined_code', 'string', ['length' => 60, 'precision' => 0]);
        $table->addColumn('code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('country_code', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('region_id', 'integer', ['precision' => 0]);
        $table->addColumn('name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['region_id'], 'idx_region', []);
        $table->addUniqueIndex(['combined_code'], 'unq_code');
    }

    /**
     * Create orocrm_magento_cart_address table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCartAddressTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_cart_address');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500, 'precision' => 0]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500, 'precision' => 0]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('created', 'datetime', ['precision' => 0]);
        $table->addColumn('updated', 'datetime', ['precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['country_code'], 'IDX_6978F651F026BB7C', []);
        $table->addIndex(['region_code'], 'IDX_6978F651AEB327AF', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_magento_order table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoOrderTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_order');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('cart_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('increment_id', 'string', ['length' => 60, 'precision' => 0]);
        $table->addColumn('is_virtual', 'boolean', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('is_guest', 'boolean', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('gift_message', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('remote_ip', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('store_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('total_paid_amount', 'float', ['notnull' => false, 'precision' => 0]);
        $table->addColumn(
            'total_invoiced_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'total_refunded_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'total_canceled_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('notes', 'text', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('feedback', 'text', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('customer_email', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10, 'precision' => 0]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('payment_details', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn(
            'subtotal_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'shipping_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('shipping_method', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn(
            'tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'discount_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'discount_percent',
            'percent',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:percent)']
        );
        $table->addColumn(
            'total_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('status', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('created_at', 'datetime', ['precision' => 0]);
        $table->addColumn('updated_at', 'datetime', ['precision' => 0]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('coupon_code', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('imported_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('synced_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addIndex(['customer_id'], 'IDX_4D09F3059395C3F3', []);
        $table->addIndex(['store_id'], 'IDX_4D09F305B092A811', []);
        $table->addIndex(['cart_id'], 'IDX_4D09F3051AD5CDBF', []);
        $table->addIndex(['user_owner_id'], 'IDX_4D09F3059EB185F9', []);
        $table->addIndex(['channel_id'], 'IDX_4D09F30572F5A1AA', []);
        $table->addIndex(['data_channel_id'], 'IDX_4D09F305BDC09B73', []);
        $table->addIndex(['organization_id'], 'IDX_4D09F30532C8A3DE', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['created_at'], 'mageorder_created_idx', []);
        $table->addUniqueIndex(['increment_id', 'channel_id'], 'unq_increment_id_channel_id');
    }

    /**
     * Create orocrm_magento_order_calls table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoOrderCallsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_order_calls');
        $table->addColumn('order_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->addIndex(['order_id'], 'IDX_A885A348D9F6D38', []);
        $table->addIndex(['call_id'], 'IDX_A885A3450A89B2C', []);
        $table->setPrimaryKey(['order_id', 'call_id']);
    }

    /**
     * Create orocrm_magento_order_emails table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoOrderEmailsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_order_emails');
        $table->addColumn('order_id', 'integer', []);
        $table->addColumn('email_id', 'integer', []);
        $table->addIndex(['order_id'], 'IDX_10E2A9508D9F6D38', []);
        $table->addIndex(['email_id'], 'IDX_10E2A950A832C1C9', []);
        $table->setPrimaryKey(['order_id', 'email_id']);
    }

    /**
     * Create orocrm_magento_customer_group table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCustomerGroupTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_customer_group');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addIndex(['channel_id'], 'IDX_71E09CA872F5A1AA', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_magento_customer table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCustomerTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_customer');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_group_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('gender', 'string', ['notnull' => false, 'length' => 8, 'precision' => 0]);
        $table->addColumn('birthday', 'date', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('created_at', 'datetime', ['precision' => 0]);
        $table->addColumn('updated_at', 'datetime', ['precision' => 0]);
        $table->addColumn('is_active', 'boolean', ['precision' => 0]);
        $table->addColumn('vat', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('lifetime', 'money', ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('rfm_recency', 'integer', ['notnull' => false]);
        $table->addColumn('rfm_frequency', 'integer', ['notnull' => false]);
        $table->addColumn('rfm_monetary', 'integer', ['notnull' => false]);
        $table->addColumn('sync_state', 'integer', ['notnull' => false]);
        $table->addColumn('password', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('created_in', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('is_confirmed', 'boolean', ['notnull' => false]);
        $table->addColumn('is_guest', 'boolean', ['notnull' => true, 'default' => false]);
        $table->addColumn('imported_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('synced_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addIndex(['website_id'], 'IDX_2A61EE7D18F45C82', []);
        $table->addIndex(['store_id'], 'IDX_2A61EE7DB092A811', []);
        $table->addIndex(['customer_group_id'], 'IDX_2A61EE7DD2919A68', []);
        $table->addIndex(['contact_id'], 'IDX_2A61EE7DE7A1254A', []);
        $table->addIndex(['account_id'], 'IDX_2A61EE7D9B6B5FBA', []);
        $table->addIndex(['user_owner_id'], 'IDX_2A61EE7D9EB185F9', []);
        $table->addIndex(['channel_id'], 'IDX_2A61EE7D72F5A1AA', []);
        $table->addIndex(['data_channel_id'], 'IDX_2A61EE7DBDC09B73', []);
        $table->addIndex(['organization_id'], 'IDX_2A61EE7D32C8A3DE', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['first_name', 'last_name'], 'magecustomer_name_idx', []);
        $table->addIndex(['last_name', 'first_name'], 'magecustomer_rev_name_idx', []);
        $table->addIndex(['email'], 'magecustomer_email_guest_idx', []);
        $table->addUniqueIndex(['origin_id', 'channel_id'], 'magecustomer_oid_cid_unq');
    }

    /**
     * Create orocrm_magento_cart_item table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCartItemTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_cart_item');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('cart_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_id', 'integer', ['precision' => 0, 'unsigned' => true]);
        $table->addColumn('parent_item_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('free_shipping', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('gift_message', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('tax_class_id', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('description', 'text', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('is_virtual', 'boolean', ['precision' => 0]);
        $table->addColumn(
            'custom_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'price_incl_tax',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('row_total', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn('tax_amount', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn('product_type', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('product_image_url', 'text', ['notnull' => false]);
        $table->addColumn('product_url', 'text', ['notnull' => false]);
        $table->addColumn('sku', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('qty', 'float', ['precision' => 0]);
        $table->addColumn('price', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn('discount_amount', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn('tax_percent', 'percent', ['precision' => 0, 'comment' => '(DC2Type:percent)']);
        $table->addColumn('weight', 'float', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('createdAt', 'datetime', ['precision' => 0]);
        $table->addColumn('updatedAt', 'datetime', ['precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('is_removed', 'boolean', ['notnull' => true, 'default' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['cart_id'], 'IDX_A73DC8621AD5CDBF', []);
        $table->addIndex(['owner_id'], 'IDX_A73DC8627E3C61F9', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['origin_id'], 'magecartitem_origin_idx', []);
        $table->addIndex(['sku'], 'magecartitem_sku_idx', []);
    }

    /**
     * Create orocrm_magento_customer_addr table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCustomerAddrTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_customer_addr');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_contact_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_contact_phone_id', 'integer', ['notnull' => false]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500, 'precision' => 0]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500, 'precision' => 0]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('created', 'datetime', ['precision' => 0]);
        $table->addColumn('updated', 'datetime', ['precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('sync_state', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_id'], 'IDX_1E239D647E3C61F9', []);
        $table->addUniqueIndex(['related_contact_address_id'], 'UNIQ_1E239D648137CB7B');
        $table->addUniqueIndex(['related_contact_phone_id'], 'UNIQ_1E239D64E3694F65');
        $table->addIndex(['country_code'], 'IDX_1E239D64F026BB7C', []);
        $table->addIndex(['region_code'], 'IDX_1E239D64AEB327AF', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_magento_cust_addr_type table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCustAddrTypeTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_cust_addr_type');
        $table->addColumn('customer_address_id', 'integer', []);
        $table->addColumn('type_name', 'string', ['length' => 16]);
        $table->addIndex(['customer_address_id'], 'IDX_308A31F187EABF7', []);
        $table->addIndex(['type_name'], 'IDX_308A31F1892CBB0E', []);
        $table->setPrimaryKey(['customer_address_id', 'type_name']);
    }

    /**
     * Create orocrm_magento_order_address table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoOrderAddressTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_order_address');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('fax', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500, 'precision' => 0]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addIndex(['owner_id'], 'IDX_E31C6DEC7E3C61F9', []);
        $table->addIndex(['country_code'], 'IDX_E31C6DECF026BB7C', []);
        $table->addIndex(['region_code'], 'IDX_E31C6DECAEB327AF', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_magento_order_addr_type table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoOrderAddrTypeTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_order_addr_type');
        $table->addColumn('order_address_id', 'integer', []);
        $table->addColumn('type_name', 'string', ['length' => 16]);
        $table->addIndex(['order_address_id'], 'IDX_E927A18F466D5220', []);
        $table->addIndex(['type_name'], 'IDX_E927A18F892CBB0E', []);
        $table->setPrimaryKey(['order_address_id', 'type_name']);
    }

    /**
     * Create orocrm_magento_product table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoProductTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_product');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('sku', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('type', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn(
            'special_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('price', 'money', ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn('created_at', 'datetime', ['precision' => 0]);
        $table->addColumn('updated_at', 'datetime', ['precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['precision' => 0, 'unsigned' => true]);
        $table->addColumn('cost', 'money', ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addIndex(['channel_id'], 'IDX_5A17298272F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['sku', 'channel_id'], 'unq_sku_channel_id');
    }

    /**
     * Create orocrm_magento_prod_to_website table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoProdToWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_prod_to_website');
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('website_id', 'integer', []);
        $table->addIndex(['product_id'], 'IDX_9BB836554584665A', []);
        $table->addIndex(['website_id'], 'IDX_9BB8365518F45C82', []);
        $table->setPrimaryKey(['product_id', 'website_id']);
    }

    /**
     * Create orocrm_magento_website table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoWebsiteTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_website');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('website_code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('website_name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('sort_order', 'integer', ['notnull' => false]);
        $table->addColumn('is_default', 'boolean', ['notnull' => false]);
        $table->addColumn('default_group_id', 'integer', ['notnull' => false]);
        $table->addIndex(['channel_id'], 'IDX_CE3270C872F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['website_name'], 'orocrm_magento_website_name_idx', []);
        $table->addUniqueIndex(['website_code', 'origin_id', 'channel_id'], 'unq_site_idx');
    }

    /**
     * Create orocrm_magento_cart table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCartTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_cart');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('items_qty', 'float', ['precision' => 0]);
        $table->addColumn('items_count', 'integer', ['precision' => 0, 'unsigned' => true]);
        $table->addColumn('base_currency_code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('store_currency_code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('quote_currency_code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('store_to_base_rate', 'float', ['precision' => 0]);
        $table->addColumn('store_to_quote_rate', 'float', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('gift_message', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('is_guest', 'boolean', ['precision' => 0]);
        $table->addColumn('payment_details', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('notes', 'text', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('status_message', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('sub_total', 'money', ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn(
            'grand_total',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('createdAt', 'datetime', ['precision' => 0]);
        $table->addColumn('updatedAt', 'datetime', ['precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('imported_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('synced_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addIndex(['customer_id'], 'IDX_96661A809395C3F3', []);
        $table->addIndex(['store_id'], 'IDX_96661A80B092A811', []);
        $table->addIndex(['shipping_address_id'], 'IDX_96661A804D4CFF2B', []);
        $table->addIndex(['billing_address_id'], 'IDX_96661A8079D0C0E4', []);
        $table->addIndex(['status_name'], 'IDX_96661A806625D392', []);
        $table->addIndex(['opportunity_id'], 'IDX_96661A809A34590F', []);
        $table->addIndex(['user_owner_id'], 'IDX_96661A809EB185F9', []);
        $table->addIndex(['channel_id'], 'IDX_96661A8072F5A1AA', []);
        $table->addIndex(['data_channel_id'], 'IDX_96661A80BDC09B73', []);
        $table->addIndex(['organization_id'], 'IDX_96661A8032C8A3DE', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['origin_id'], 'magecart_origin_idx', []);
        $table->addIndex(['updatedAt'], 'magecart_updated_idx', []);
        $table->addUniqueIndex(['origin_id', 'channel_id'], 'unq_cart_origin_id_channel_id');
    }

    /**
     * Create orocrm_magento_cart_calls table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCartCallsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_cart_calls');
        $table->addColumn('cart_id', 'integer', []);
        $table->addColumn('call_id', 'integer', []);
        $table->addIndex(['cart_id'], 'IDX_83A847751AD5CDBF', []);
        $table->addIndex(['call_id'], 'IDX_83A8477550A89B2C', []);
        $table->setPrimaryKey(['cart_id', 'call_id']);
    }

    /**
     * Create orocrm_magento_cart_emails table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCartEmailsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_cart_emails');
        $table->addColumn('cart_id', 'integer', []);
        $table->addColumn('email_id', 'integer', []);
        $table->addIndex(['cart_id'], 'IDX_11B0F84B1AD5CDBF', []);
        $table->addIndex(['email_id'], 'IDX_11B0F84BA832C1C9', []);
        $table->setPrimaryKey(['cart_id', 'email_id']);
    }

    /**
     * Create orocrm_magento_store table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoStoreTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_store');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('website_id', 'integer', []);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_code', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('store_name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addIndex(['website_id'], 'IDX_477738EA18F45C82', []);
        $table->addIndex(['channel_id'], 'IDX_477738EA72F5A1AA', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['store_code', 'channel_id'], 'unq_code_channel_id');
    }

    /**
     * Create orocrm_magento_cart_status table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoCartStatusTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_cart_status');
        $table->addColumn('name', 'string', ['length' => 32, 'precision' => 0]);
        $table->addColumn('label', 'string', ['length' => 255, 'precision' => 0]);
        $table->addUniqueIndex(['label'], 'UNIQ_26317505EA750E8');
        $table->setPrimaryKey(['name']);
    }

    /**
     * Create orocrm_magento_order_items table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoOrderItemsTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_order_items');
        $table->addColumn('id', 'integer', ['precision' => 0, 'autoincrement' => true]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_type', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
        $table->addColumn('product_options', 'text', ['notnull' => false, 'precision' => 0]);
        $table->addColumn('is_virtual', 'boolean', ['notnull' => false, 'precision' => 0]);
        $table->addColumn(
            'original_price',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'discount_percent',
            'percent',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:percent)']
        );
        $table->addColumn('name', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('sku', 'string', ['length' => 255, 'precision' => 0]);
        $table->addColumn('qty', 'float', ['precision' => 0]);
        $table->addColumn('price', 'money', ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn('weight', 'float', ['notnull' => false, 'precision' => 0]);
        $table->addColumn(
            'tax_percent',
            'percent',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:percent)']
        );
        $table->addColumn(
            'tax_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'discount_amount',
            'money',
            ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('row_total', 'money', ['notnull' => false, 'precision' => 0, 'comment' => '(DC2Type:money)']);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['order_id'], 'IDX_3135EFF68D9F6D38', []);
        $table->addIndex(['owner_id'], 'IDX_3135EFF67E3C61F9', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_magento_newsl_subscr table
     *
     * @param Schema $schema
     */
    protected function createOrocrmMagentoNewslSubscrTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_magento_newsl_subscr');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('change_status_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addColumn('confirm_code', 'string', ['notnull' => false, 'length' => 32]);
        $table->setPrimaryKey(['id']);

        $this->extendExtension->addEnumField(
            $schema,
            $table,
            'status',
            'mage_subscr_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_CUSTOM]
            ]
        );
    }

    /**
     * Add orocrm_magento_cart_address foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCartAddressForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_cart_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            []
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            []
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_order foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoOrderForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            []
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_4D09F305BDC09B73'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_4D09F30532C8A3DE'
        );
    }

    /**
     * Add orocrm_magento_order_calls foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoOrderCallsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order_calls');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_magento_order_emails foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoOrderEmailsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order_emails');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email'),
            ['email_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_magento_customer_group foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCustomerGroupForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_customer_group');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_customer foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCustomerForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer_group'),
            ['customer_group_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_2A61EE7DBDC09B73'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_2A61EE7D32C8A3DE'
        );
    }

    /**
     * Add orocrm_magento_cart_item foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCartItemForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_cart_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_customer_addr foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCustomerAddrForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_customer_addr');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_address'),
            ['related_contact_address_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_phone'),
            ['related_contact_phone_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            []
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            []
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_cust_addr_type foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCustAddrTypeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_cust_addr_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer_addr'),
            ['customer_address_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'FK_308A31F187EABF7'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address_type'),
            ['type_name'],
            ['name'],
            []
        );
    }

    /**
     * Add orocrm_magento_order_address foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoOrderAddressForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            []
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            []
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_order_addr_type foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoOrderAddrTypeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order_addr_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order_address'),
            ['order_address_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'FK_E927A18F466D5220'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address_type'),
            ['type_name'],
            ['name'],
            []
        );
    }

    /**
     * Add orocrm_magento_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_prod_to_website foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoProdToWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_prod_to_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_magento_website foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoWebsiteForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_cart foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCartForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_cart');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart_address'),
            ['shipping_address_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart_address'),
            ['billing_address_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart_status'),
            ['status_name'],
            ['name'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_opportunity'),
            ['opportunity_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_96661A80BDC09B73'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_96661A8032C8A3DE'
        );
    }

    /**
     * Add orocrm_magento_cart_calls foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCartCallsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_cart_calls');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_call'),
            ['call_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_magento_cart_emails foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoCartEmailsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_cart_emails');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email'),
            ['email_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orocrm_magento_store foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoStoreForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_store');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'cascade']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_order_items foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoOrderItemsForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order_items');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * Add orocrm_magento_newsl_subscr foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmMagentoNewslSubscrForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_newsl_subscr');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['customer_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Enable activities
     *
     * @param Schema $schema
     */
    protected function addActivityAssociations(Schema $schema)
    {
        $this->activityExtension->addActivityAssociation($schema, 'oro_email', 'orocrm_magento_customer');
        $this->activityExtension->addActivityAssociation($schema, 'orocrm_call', 'orocrm_magento_customer');
        $this->activityExtension->addActivityAssociation($schema, 'oro_calendar_event', 'orocrm_magento_customer');

        CreateActivityAssociation::addActivityAssociations($schema, $this->activityExtension);
        OrderActivityAssociation::addActivityAssociations($schema, $this->activityExtension);
    }

    /**
     * @param Schema $schema
     */
    protected function addIdentifierEventAssociations(Schema $schema)
    {
        $this->identifierEventExtension->addIdentifierAssociation($schema, 'orocrm_magento_customer');
        $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_cart');
        $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_customer');
        $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_order');
        $this->visitExtension->addVisitEventAssociation($schema, 'orocrm_magento_product');
    }
}
