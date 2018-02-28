<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_41_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class SetSkuColumnsOptionsToNull implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_cart_item');
        $table->getColumn('sku')->setOptions(['notnull' => false]);

        $table = $schema->getTable('orocrm_magento_order_items');
        $table->getColumn('sku')->setOptions(['notnull' => false]);

        $table = $schema->getTable('orocrm_magento_product');
        $table->getColumn('sku')->setOptions(['notnull' => false]);
        $table->dropIndex('unq_sku_channel_id');
    }
}
