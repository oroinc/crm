<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_20;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_order_addr_type');
        $table->removeForeignKey('FK_E927A18F466D5220');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_order_address'),
            ['order_address_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'FK_E927A18F466D5220'
        );

        $table = $schema->getTable('orocrm_magento_cust_addr_type');
        $table->removeForeignKey('FK_308A31F187EABF7');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_magento_customer_addr'),
            ['customer_address_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            'FK_308A31F187EABF7'
        );
    }
}
