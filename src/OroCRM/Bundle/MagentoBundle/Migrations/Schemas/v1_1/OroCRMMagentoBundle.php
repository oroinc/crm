<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schemas\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migrations\Migration;

class OroCRMMagentoBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema)
    {
        return [
            "ALTER TABLE orocrm_magento_customer_address RENAME TO orocrm_magento_customer_addr;",
            "ALTER TABLE orocrm_magento_customer_address_to_address_type RENAME TO orocrm_magento_cust_addr_type;",
            "ALTER TABLE orocrm_magento_product_to_website RENAME TO orocrm_magento_prod_to_website;",
        ];
    }
}
