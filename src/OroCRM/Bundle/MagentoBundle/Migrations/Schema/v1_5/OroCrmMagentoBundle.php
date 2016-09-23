<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCrmMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $cartTable = $schema->getTable('oro_magento_cart');
        $cartTable->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $cartTable->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);

        $orderTable = $schema->getTable('oro_magento_order');
        $orderTable->addColumn('first_name', 'string', ['notnull' => false, 'length' => 255]);
        $orderTable->addColumn('last_name', 'string', ['notnull' => false, 'length' => 255]);

        $customerTable = $schema->getTable('oro_magento_customer');
        $customerTable->getColumn('birthday')->setType(Type::getType(Type::DATE));
    }
}
