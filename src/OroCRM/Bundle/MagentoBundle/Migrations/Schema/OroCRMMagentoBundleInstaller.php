<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_0\OroCRMMagentoBundle;

class OroCRMMagentoBundleInstaller implements Installation
{
    /**
     * @inheritdoc
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        OroCRMMagentoBundle::orocrmMagentoCartTable($schema);
        OroCRMMagentoBundle::orocrmMagentoCartAddressTable($schema);
        OroCRMMagentoBundle::orocrmMagentoCartCallsTable($schema);
        OroCRMMagentoBundle::orocrmMagentoCartEmailsTable($schema);
        OroCRMMagentoBundle::orocrmMagentoCartItemTable($schema);
        OroCRMMagentoBundle::orocrmMagentoCartStatusTable($schema);
        OroCRMMagentoBundle::orocrmMagentoCustomerTable($schema);
        OroCRMMagentoBundle::orocrmMagentoCustomerAddressTable($schema, 'orocrm_magento_customer_addr');
        OroCRMMagentoBundle::orocrmMagentoCustomerAddressToAddressTypeTable($schema, 'orocrm_magento_cust_addr_type');
        OroCRMMagentoBundle::orocrmMagentoCustomerGroupTable($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderTable($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderAddressTable($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderAddrTypeTable($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderCallsTable($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderEmailsTable($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderItemsTable($schema);
        OroCRMMagentoBundle::orocrmMagentoProductTable($schema);
        OroCRMMagentoBundle::orocrmMagentoProductToWebsiteTable($schema, 'orocrm_magento_prod_to_website');
        OroCRMMagentoBundle::orocrmMagentoRegionTable($schema);
        OroCRMMagentoBundle::orocrmMagentoStoreTable($schema);
        OroCRMMagentoBundle::orocrmMagentoWebsiteTable($schema);
        OroCRMMagentoBundle::updateOroIntegrationTransportTable($schema);

        OroCRMMagentoBundle::orocrmMagentoCartForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoCartAddressForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoCartCallsForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoCartEmailsForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoCartItemForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoCustomerForeignKeys($schema);
        OroCRMMagentoBundle::orocrMagentoCustomerAddressForeignKeys($schema, 'orocrm_magento_customer_addr');
        OroCRMMagentoBundle::orocrmMagentoCustomerAddressToAddressTypeForeignKeys(
            $schema,
            'orocrm_magento_cust_addr_type',
            'orocrm_magento_customer_addr'
        );
        OroCRMMagentoBundle::orocrmMagentoCustomerGroupForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderAddressForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderAddrTypeForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderCallsForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderEmailsForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoOrderItemsForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoProductForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoProductToWebsiteForeignKeys($schema, 'orocrm_magento_prod_to_website');
        OroCRMMagentoBundle::orocrmMagentoStoreForeignKeys($schema);
        OroCRMMagentoBundle::orocrmMagentoWebsiteForeignKeys($schema);
    }
}
