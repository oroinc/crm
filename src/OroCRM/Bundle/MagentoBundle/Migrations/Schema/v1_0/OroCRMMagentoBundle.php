<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroCRMMagentoBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::orocrmMagentoCartTable($schema);
        self::orocrmMagentoCartAddressTable($schema);
       
        self::orocrmMagentoCartEmailsTable($schema);
        self::orocrmMagentoCartItemTable($schema);
        self::orocrmMagentoCartStatusTable($schema);
        self::orocrmMagentoCustomerTable($schema);
        self::orocrmMagentoCustomerAddressTable($schema);
        self::orocrmMagentoCustomerAddressToAddressTypeTable($schema);
        self::orocrmMagentoCustomerGroupTable($schema);
        self::orocrmMagentoOrderTable($schema);
        self::orocrmMagentoOrderAddressTable($schema);
        self::orocrmMagentoOrderAddrTypeTable($schema);
        
        self::orocrmMagentoOrderEmailsTable($schema);
        self::orocrmMagentoOrderItemsTable($schema);
        self::orocrmMagentoProductTable($schema);
        self::orocrmMagentoProductToWebsiteTable($schema);
        self::orocrmMagentoRegionTable($schema);
        self::orocrmMagentoStoreTable($schema);
        self::orocrmMagentoWebsiteTable($schema);
        self::updateOroIntegrationTransportTable($schema);

        self::orocrmMagentoCartForeignKeys($schema);
        self::orocrmMagentoCartAddressForeignKeys($schema);
        
        self::orocrmMagentoCartEmailsForeignKeys($schema);
        self::orocrmMagentoCartItemForeignKeys($schema);
        self::orocrmMagentoCustomerForeignKeys($schema);
        self::orocrMagentoCustomerAddressForeignKeys($schema);
        self::orocrmMagentoCustomerAddressToAddressTypeForeignKeys($schema);
        self::orocrmMagentoCustomerGroupForeignKeys($schema);
        self::orocrmMagentoOrderForeignKeys($schema);
        self::orocrmMagentoOrderAddressForeignKeys($schema);
        self::orocrmMagentoOrderAddrTypeForeignKeys($schema);
        
        self::orocrmMagentoOrderEmailsForeignKeys($schema);
        self::orocrmMagentoOrderItemsForeignKeys($schema);
        self::orocrmMagentoProductForeignKeys($schema);
        self::orocrmMagentoProductToWebsiteForeignKeys($schema);
        self::orocrmMagentoStoreForeignKeys($schema);
        self::orocrmMagentoWebsiteForeignKeys($schema);
    }

    public static function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('wsdl_url', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('api_user', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('api_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('sync_start_date', 'date', ['notnull' => false]);
        $table->addColumn('sync_range', 'string', ['notnull' => false, 'length' => 50]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('websites', 'array', ['notnull' => false, 'comment' => '(DC2Type:array)']);
        $table->addColumn('is_extension_installed', 'boolean', ['notnull' => false]);
        $table->addColumn('is_wsi_mode', 'boolean', ['notnull' => false]);
    }

    /**
     * Generate table orocrm_magento_cart
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartTable(Schema $schema)
    {
        /** Generate table orocrm_magento_cart **/
        $table = $schema->createTable('orocrm_magento_cart');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'smallint', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('shipping_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('status_name', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('billing_address_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('opportunity_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('items_qty', 'integer', ['unsigned' => true]);
        $table->addColumn('items_count', 'integer', ['unsigned' => true]);
        $table->addColumn('base_currency_code', 'string', ['length' => 32]);
        $table->addColumn('store_currency_code', 'string', ['length' => 32]);
        $table->addColumn('quote_currency_code', 'string', ['length' => 32]);
        $table->addColumn('store_to_base_rate', 'float', []);
        $table->addColumn('store_to_quote_rate', 'float', ['notnull' => false]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('gift_message', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('is_guest', 'boolean', []);
        $table->addColumn('payment_details', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addColumn('sub_total', 'float', ['notnull' => false]);
        $table->addColumn('grand_total', 'float', ['notnull' => false]);
        $table->addColumn('tax_amount', 'float', ['notnull' => false]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_96661A801023C4EE');
        $table->addUniqueIndex(['origin_id', 'channel_id'], 'unq_cart_origin_id_channel_id');
        $table->addIndex(['customer_id'], 'IDX_96661A809395C3F3', []);
        $table->addIndex(['store_id'], 'IDX_96661A80B092A811', []);
        $table->addIndex(['shipping_address_id'], 'IDX_96661A804D4CFF2B', []);
        $table->addIndex(['billing_address_id'], 'IDX_96661A8079D0C0E4', []);
        $table->addIndex(['status_name'], 'IDX_96661A806625D392', []);
        $table->addIndex(['opportunity_id'], 'IDX_96661A809A34590F', []);
        $table->addIndex(['workflow_step_id'], 'IDX_96661A8071FE882C', []);
        $table->addIndex(['channel_id'], 'IDX_96661A8072F5A1AA', []);
        $table->addIndex(['origin_id'], 'magecart_origin_idx', []);
        /** End of generate table orocrm_magento_cart **/
    }

    /**
     * Generate table orocrm_magento_cart_address
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartAddressTable(Schema $schema)
    {
        /** Generate table orocrm_magento_cart_address **/
        $table = $schema->createTable('orocrm_magento_cart_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created', 'datetime', []);
        $table->addColumn('updated', 'datetime', []);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['country_code'], 'IDX_6978F651F026BB7C', []);
        $table->addIndex(['region_code'], 'IDX_6978F651AEB327AF', []);
        /** End of generate table orocrm_magento_cart_address **/
    }

    
    /**
     * Generate table orocrm_magento_cart_emails
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartEmailsTable(Schema $schema)
    {
        /** Generate table orocrm_magento_cart_emails **/
        $table = $schema->createTable('orocrm_magento_cart_emails');
        $table->addColumn('cart_id', 'integer', []);
        $table->addColumn('email_id', 'integer', []);
        $table->setPrimaryKey(['cart_id', 'email_id']);
        $table->addIndex(['cart_id'], 'IDX_11B0F84B1AD5CDBF', []);
        $table->addIndex(['email_id'], 'IDX_11B0F84BA832C1C9', []);
        /** End of generate table orocrm_magento_cart_emails **/
    }

    /**
     * Generate table orocrm_magento_cart_item
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartItemTable(Schema $schema)
    {
        /** Generate table orocrm_magento_cart_item **/
        $table = $schema->createTable('orocrm_magento_cart_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('cart_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_id', 'integer', ['unsigned' => true]);
        $table->addColumn('parent_item_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('free_shipping', 'string', ['length' => 255]);
        $table->addColumn('gift_message', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('tax_class_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('is_virtual', 'boolean', []);
        $table->addColumn('custom_price', 'float', ['notnull' => false]);
        $table->addColumn('price_incl_tax', 'float', ['notnull' => false]);
        $table->addColumn('row_total', 'float', []);
        $table->addColumn('tax_amount', 'float', []);
        $table->addColumn('product_type', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('qty', 'float', []);
        $table->addColumn('price', 'float', []);
        $table->addColumn('discount_amount', 'float', []);
        $table->addColumn('tax_percent', 'float', []);
        $table->addColumn('weight', 'float', ['notnull' => false]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['cart_id'], 'IDX_A73DC8621AD5CDBF', []);
        $table->addIndex(['origin_id'], 'magecartitem_origin_idx', []);
        $table->addIndex(['sku'], 'magecartitem_sku_idx', []);
        /** End of generate table orocrm_magento_cart_item **/
    }

    /**
     * Generate table orocrm_magento_cart_status
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartStatusTable(Schema $schema)
    {
        /** Generate table orocrm_magento_cart_status **/
        $table = $schema->createTable('orocrm_magento_cart_status');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'UNIQ_26317505EA750E8');
        /** End of generate table orocrm_magento_cart_status **/
    }

    /**
     * Generate table orocrm_magento_customer
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCustomerTable(Schema $schema)
    {
        /** Generate table orocrm_magento_customer **/
        $table = $schema->createTable('orocrm_magento_customer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'smallint', ['notnull' => false]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_group_id', 'integer', ['notnull' => false]);
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('gender', 'string', ['notnull' => false, 'length' => 8]);
        $table->addColumn('birthday', 'datetime', ['notnull' => false]);
        $table->addColumn('email', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('is_active', 'boolean', []);
        $table->addColumn('vat', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['origin_id', 'channel_id'], 'unq_origin_id_channel_id');
        $table->addIndex(['website_id'], 'IDX_2A61EE7D18F45C82', []);
        $table->addIndex(['store_id'], 'IDX_2A61EE7DB092A811', []);
        $table->addIndex(['customer_group_id'], 'IDX_2A61EE7DD2919A68', []);
        $table->addIndex(['contact_id'], 'IDX_2A61EE7DE7A1254A', []);
        $table->addIndex(['account_id'], 'IDX_2A61EE7D9B6B5FBA', []);
        $table->addIndex(['channel_id'], 'IDX_2A61EE7D72F5A1AA', []);
        /** End of generate table orocrm_magento_customer **/
    }

    /**
     * Generate table orocrm_magento_customer_address
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmMagentoCustomerAddressTable(Schema $schema, $tableName = null)
    {
        /** Generate table orocrm_magento_customer_address **/
        $table = $schema->createTable($tableName ?: 'orocrm_magento_customer_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('label', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('street2', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_prefix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('middle_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('name_suffix', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('is_primary', 'boolean', ['notnull' => false]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('created', 'datetime', []);
        $table->addColumn('updated', 'datetime', []);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_55153CAD7E3C61F9', []);
        $table->addIndex(['country_code'], 'IDX_55153CADF026BB7C', []);
        $table->addIndex(['region_code'], 'IDX_55153CADAEB327AF', []);
        /** End of generate table orocrm_magento_customer_address **/
    }

    /**
     * Generate table orocrm_magento_customer_address_to_address_type
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmMagentoCustomerAddressToAddressTypeTable(Schema $schema, $tableName = null)
    {
        /** Generate table orocrm_magento_customer_address_to_address_type **/
        $table = $schema->createTable($tableName ?: 'orocrm_magento_customer_address_to_address_type');
        $table->addColumn('customer_address_id', 'integer', []);
        $table->addColumn('type_name', 'string', ['length' => 16]);
        $table->setPrimaryKey(['customer_address_id', 'type_name']);
        $table->addIndex(['customer_address_id'], 'IDX_65B2C97487EABF7', []);
        $table->addIndex(['type_name'], 'IDX_65B2C974892CBB0E', []);
        /** End of generate table orocrm_magento_customer_address_to_address_type **/
    }

    /**
     * Generate table orocrm_magento_customer_group
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCustomerGroupTable(Schema $schema)
    {
        /** Generate table orocrm_magento_customer_group **/
        $table = $schema->createTable('orocrm_magento_customer_group');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'smallint', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['channel_id'], 'IDX_71E09CA872F5A1AA', []);
        /** End of generate table orocrm_magento_customer_group **/
    }

    /**
     * Generate table orocrm_magento_order
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderTable(Schema $schema)
    {
        /** Generate table orocrm_magento_order **/
        $table = $schema->createTable('orocrm_magento_order');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'smallint', ['notnull' => false]);
        $table->addColumn('workflow_item_id', 'integer', ['notnull' => false]);
        $table->addColumn('cart_id', 'integer', ['notnull' => false]);
        $table->addColumn('workflow_step_id', 'integer', ['notnull' => false]);
        $table->addColumn('customer_id', 'integer', ['notnull' => false]);
        $table->addColumn('store_id', 'integer', ['notnull' => false]);
        $table->addColumn('increment_id', 'string', ['length' => 60]);
        $table->addColumn('is_virtual', 'boolean', ['notnull' => false]);
        $table->addColumn('is_guest', 'boolean', ['notnull' => false]);
        $table->addColumn('gift_message', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('remote_ip', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('store_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('total_paid_amount', 'float', ['notnull' => false]);
        $table->addColumn('total_invoiced_amount', 'float', ['notnull' => false]);
        $table->addColumn('total_refunded_amount', 'float', ['notnull' => false]);
        $table->addColumn('total_canceled_amount', 'float', ['notnull' => false]);
        $table->addColumn('notes', 'text', ['notnull' => false]);
        $table->addColumn('feedback', 'text', ['notnull' => false]);
        $table->addColumn('currency', 'string', ['notnull' => false, 'length' => 10]);
        $table->addColumn('payment_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('payment_details', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('subtotal_amount', 'float', ['notnull' => false]);
        $table->addColumn('shipping_amount', 'float', ['notnull' => false]);
        $table->addColumn('shipping_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('tax_amount', 'float', ['notnull' => false]);
        $table->addColumn('discount_amount', 'float', ['notnull' => false]);
        $table->addColumn('discount_percent', 'float', ['notnull' => false]);
        $table->addColumn('total_amount', 'float', ['notnull' => false]);
        $table->addColumn('status', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['cart_id'], 'UNIQ_4D09F3051AD5CDBF');
        $table->addUniqueIndex(['workflow_item_id'], 'UNIQ_4D09F3051023C4EE');
        $table->addUniqueIndex(['increment_id', 'channel_id'], 'unq_increment_id_channel_id');
        $table->addIndex(['customer_id'], 'IDX_4D09F3059395C3F3', []);
        $table->addIndex(['store_id'], 'IDX_4D09F305B092A811', []);
        $table->addIndex(['workflow_step_id'], 'IDX_4D09F30571FE882C', []);
        $table->addIndex(['channel_id'], 'IDX_4D09F30572F5A1AA', []);
        /** End of generate table orocrm_magento_order **/
    }

    /**
     * Generate table orocrm_magento_order_address
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderAddressTable(Schema $schema)
    {
        /** Generate table orocrm_magento_order_address **/
        $table = $schema->createTable('orocrm_magento_order_address');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('region_code', 'string', ['notnull' => false, 'length' => 16]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('country_code', 'string', ['notnull' => false, 'length' => 2]);
        $table->addColumn('fax', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('street', 'string', ['notnull' => false, 'length' => 500]);
        $table->addColumn('city', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('postal_code', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('organization', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('region_text', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'IDX_E31C6DEC7E3C61F9', []);
        $table->addIndex(['country_code'], 'IDX_E31C6DECF026BB7C', []);
        $table->addIndex(['region_code'], 'IDX_E31C6DECAEB327AF', []);
        /** End of generate table orocrm_magento_order_address **/
    }

    /**
     * Generate table orocrm_magento_order_addr_type
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderAddrTypeTable(Schema $schema)
    {
        /** Generate table orocrm_magento_order_addr_type **/
        $table = $schema->createTable('orocrm_magento_order_addr_type');
        $table->addColumn('order_address_id', 'integer', []);
        $table->addColumn('type_name', 'string', ['length' => 16]);
        $table->setPrimaryKey(['order_address_id', 'type_name']);
        $table->addIndex(['order_address_id'], 'IDX_7B667960466D5220', []);
        $table->addIndex(['type_name'], 'IDX_7B667960892CBB0E', []);
        /** End of generate table orocrm_magento_order_addr_type **/
    }

    

    /**
     * Generate table orocrm_magento_order_emails
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderEmailsTable(Schema $schema)
    {
        /** Generate table orocrm_magento_order_emails **/
        $table = $schema->createTable('orocrm_magento_order_emails');
        $table->addColumn('order_id', 'integer', []);
        $table->addColumn('email_id', 'integer', []);
        $table->setPrimaryKey(['order_id', 'email_id']);
        $table->addIndex(['order_id'], 'IDX_10E2A9508D9F6D38', []);
        $table->addIndex(['email_id'], 'IDX_10E2A950A832C1C9', []);
        /** End of generate table orocrm_magento_order_emails **/
    }

    /**
     * Generate table orocrm_magento_order_items
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderItemsTable(Schema $schema)
    {
        /** Generate table orocrm_magento_order_items **/
        $table = $schema->createTable('orocrm_magento_order_items');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('order_id', 'integer', ['notnull' => false]);
        $table->addColumn('product_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('product_options', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('is_virtual', 'boolean', ['notnull' => false]);
        $table->addColumn('original_price', 'float', ['notnull' => false]);
        $table->addColumn('discount_percent', 'float', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('qty', 'integer', []);
        $table->addColumn('price', 'float', ['notnull' => false]);
        $table->addColumn('weight', 'float', ['notnull' => false]);
        $table->addColumn('tax_percent', 'float', ['notnull' => false]);
        $table->addColumn('tax_amount', 'float', ['notnull' => false]);
        $table->addColumn('discount_amount', 'float', ['notnull' => false]);
        $table->addColumn('row_total', 'float', ['notnull' => false]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['order_id'], 'IDX_3135EFF68D9F6D38', []);
        /** End of generate table orocrm_magento_order_items **/
    }

    /**
     * Generate table orocrm_magento_product
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoProductTable(Schema $schema)
    {
        /** Generate table orocrm_magento_product **/
        $table = $schema->createTable('orocrm_magento_product');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'smallint', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('sku', 'string', ['length' => 255]);
        $table->addColumn('type', 'string', ['length' => 255]);
        $table->addColumn('special_price', 'float', ['notnull' => false]);
        $table->addColumn('price', 'float', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->addColumn('origin_id', 'integer', ['unsigned' => true]);
        $table->addColumn('cost', 'float', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['sku', 'channel_id'], 'unq_sku_channel_id');
        $table->addIndex(['channel_id'], 'IDX_5A17298272F5A1AA', []);
        /** End of generate table orocrm_magento_product **/
    }

    /**
     * Generate table orocrm_magento_product_to_website
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmMagentoProductToWebsiteTable(Schema $schema, $tableName = null)
    {
        /** Generate table orocrm_magento_product_to_website **/
        $table = $schema->createTable($tableName ?: 'orocrm_magento_product_to_website');
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('website_id', 'integer', []);
        $table->setPrimaryKey(['product_id', 'website_id']);
        $table->addIndex(['product_id'], 'IDX_3A3EF4984584665A', []);
        $table->addIndex(['website_id'], 'IDX_3A3EF49818F45C82', []);
        /** End of generate table orocrm_magento_product_to_website **/
    }

    /**
     * Generate table orocrm_magento_region
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoRegionTable(Schema $schema)
    {
        /** Generate table orocrm_magento_region **/
        $table = $schema->createTable('orocrm_magento_region');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('combined_code', 'string', ['length' => 60]);
        $table->addColumn('code', 'string', ['length' => 32]);
        $table->addColumn('country_code', 'string', ['length' => 255]);
        $table->addColumn('region_id', 'integer', []);
        $table->addColumn('name', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['combined_code'], 'unq_code');
        $table->addIndex(['region_id'], 'idx_region', []);
        /** End of generate table orocrm_magento_region **/
    }

    /**
     * Generate table orocrm_magento_store
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoStoreTable(Schema $schema)
    {
        /** Generate table orocrm_magento_store **/
        $table = $schema->createTable('orocrm_magento_store');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'smallint', ['notnull' => false]);
        $table->addColumn('website_id', 'integer', []);
        $table->addColumn('store_code', 'string', ['length' => 32]);
        $table->addColumn('store_name', 'string', ['length' => 255]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['store_code', 'channel_id'], 'unq_code_channel_id');
        $table->addIndex(['website_id'], 'IDX_477738EA18F45C82', []);
        $table->addIndex(['channel_id'], 'IDX_477738EA72F5A1AA', []);
        /** End of generate table orocrm_magento_store **/
    }

    /**
     * Generate table orocrm_magento_website
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoWebsiteTable(Schema $schema)
    {
        /** Generate table orocrm_magento_website **/
        $table = $schema->createTable('orocrm_magento_website');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'smallint', ['notnull' => false]);
        $table->addColumn('website_code', 'string', ['length' => 32]);
        $table->addColumn('website_name', 'string', ['length' => 255]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['website_code', 'origin_id', 'channel_id'], 'unq_site_idx');
        $table->addIndex(['channel_id'], 'IDX_CE3270C872F5A1AA', []);
        /** End of generate table orocrm_magento_website **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_cart
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_cart **/
        $table = $schema->getTable('orocrm_magento_cart');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart_address'),
            ['shipping_address_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart_status'),
            ['status_name'],
            ['name'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart_address'),
            ['billing_address_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_opportunity'),
            ['opportunity_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_cart **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_cart_address
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartAddressForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_cart_address **/
        $table = $schema->getTable('orocrm_magento_cart_address');
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
        /** End of generate foreign keys for table orocrm_magento_cart_address **/
    }

    

    /**
     * Generate foreign keys for table orocrm_magento_cart_emails
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartEmailsForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_cart_emails **/
        $table = $schema->getTable('orocrm_magento_cart_emails');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email'),
            ['email_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_cart_emails **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_cart_item
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCartItemForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_cart_item **/
        $table = $schema->getTable('orocrm_magento_cart_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_cart_item **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_customer
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCustomerForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_customer **/
        $table = $schema->getTable('orocrm_magento_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_website'),
            ['website_id'],
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
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer_group'),
            ['customer_group_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact'),
            ['contact_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_customer **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_customer_address
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrMagentoCustomerAddressForeignKeys(Schema $schema, $tableName = null)
    {
        /** Generate foreign keys for table orocrm_magento_customer_address **/
        $table = $schema->getTable($tableName ?: 'orocrm_magento_customer_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_customer_address **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_customer_address_to_address_type
     *
     * @param Schema $schema
     * @param string $tableName
     * @param string $customerAddressTableName
     */
    public static function orocrmMagentoCustomerAddressToAddressTypeForeignKeys(
        Schema $schema,
        $tableName = null,
        $customerAddressTableName = null
    ) {
        /** Generate foreign keys for table orocrm_magento_customer_address_to_address_type **/
        $table = $schema->getTable($tableName ?: 'orocrm_magento_customer_address_to_address_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address_type'),
            ['type_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable($customerAddressTableName ?: 'orocrm_magento_customer_address'),
            ['customer_address_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_customer_address_to_address_type **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_customer_group
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoCustomerGroupForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_customer_group **/
        $table = $schema->getTable('orocrm_magento_customer_group');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_customer_group **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_order
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_order **/
        $table = $schema->getTable('orocrm_magento_order');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_item'),
            ['workflow_item_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_cart'),
            ['cart_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_workflow_step'),
            ['workflow_step_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer'),
            ['customer_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_store'),
            ['store_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_order **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_order_address
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderAddressForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_order_address **/
        $table = $schema->getTable('orocrm_magento_order_address');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_region'),
            ['region_code'],
            ['combined_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_dictionary_country'),
            ['country_code'],
            ['iso2_code'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_order_address **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_order_addr_type
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderAddrTypeForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_order_addr_type **/
        $table = $schema->getTable('orocrm_magento_order_addr_type');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_address_type'),
            ['type_name'],
            ['name'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order_address'),
            ['order_address_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_order_addr_type **/
    }

    

    /**
     * Generate foreign keys for table orocrm_magento_order_emails
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderEmailsForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_order_emails **/
        $table = $schema->getTable('orocrm_magento_order_emails');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email'),
            ['email_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => null, 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_order_emails **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_order_items
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoOrderItemsForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_order_items **/
        $table = $schema->getTable('orocrm_magento_order_items');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order'),
            ['order_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_order_items **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_product
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoProductForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_product **/
        $table = $schema->getTable('orocrm_magento_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_product **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_product_to_website
     *
     * @param Schema $schema
     * @param string $tableName
     */
    public static function orocrmMagentoProductToWebsiteForeignKeys(Schema $schema, $tableName = null)
    {
        /** Generate foreign keys for table orocrm_magento_product_to_website **/
        $table = $schema->getTable($tableName ?: 'orocrm_magento_product_to_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_product'),
            ['product_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_product_to_website **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_store
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoStoreForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_store **/
        $table = $schema->getTable('orocrm_magento_store');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_store **/
    }

    /**
     * Generate foreign keys for table orocrm_magento_website
     *
     * @param Schema $schema
     */
    public static function orocrmMagentoWebsiteForeignKeys(Schema $schema)
    {
        /** Generate foreign keys for table orocrm_magento_website **/
        $table = $schema->getTable('orocrm_magento_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
        /** End of generate foreign keys for table orocrm_magento_website **/
    }
}
