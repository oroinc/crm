<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_15;

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
        $table = $schema->getTable('orocrm_magento_customer');
        $table->addIndex(['last_name', 'first_name'], 'magecustomer_rev_name_idx');

        $table = $schema->getTable('orocrm_magento_cart_address');
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255]);
        $table = $schema->getTable('orocrm_magento_website');
        $table->addIndex(['website_name'], 'orocrm_magento_website_name_idx');
    }
}
