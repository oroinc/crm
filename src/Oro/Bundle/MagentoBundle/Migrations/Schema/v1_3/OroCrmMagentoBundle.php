<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCrmMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $customerTable = $schema->getTable('orocrm_magento_customer');
        $customerTable->addIndex(array('first_name', 'last_name'), 'magecustomer_name_idx');

        $orderTable = $schema->getTable('orocrm_magento_order');
        $orderTable->addIndex(array('created_at'), 'mageorder_created_idx');

        $cartTable = $schema->getTable('orocrm_magento_cart');
        $cartTable->addIndex(array('updatedAt'), 'magecart_updated_idx');
    }
}
