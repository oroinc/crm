<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMMagentoBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addSql(
            $queries->getRenameTableSql('orocrm_magento_customer_address', 'orocrm_magento_customer_addr')
        );
        $queries->addSql(
            $queries->getRenameTableSql(
                'orocrm_magento_customer_address_to_address_type',
                'orocrm_magento_cust_addr_type'
            )
        );
        $queries->addSql(
            $queries->getRenameTableSql('orocrm_magento_product_to_website', 'orocrm_magento_prod_to_website')
        );
    }
}
