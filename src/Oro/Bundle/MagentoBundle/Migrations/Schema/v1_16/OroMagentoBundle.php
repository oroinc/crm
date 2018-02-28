<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_16;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_cart');
        $table->getColumn('items_qty')->setType(Type::getType('float'));

        $table = $schema->getTable('orocrm_magento_order_items');
        $table->getColumn('qty')->setType(Type::getType('float'));
    }
}
