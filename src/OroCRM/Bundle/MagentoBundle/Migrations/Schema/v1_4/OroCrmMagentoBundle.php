<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Types\Type;
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
        $table = $schema->getTable('orocrm_magento_order_items');
        $table->getColumn('product_options')->setType(Type::getType(Type::TEXT))->setLength(null);
    }
}
