<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_25;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class ChangeAddressPostalCodeLength implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_customer_addr');
        $table->changeColumn('postal_code', ['length' => 255]);

        $table = $schema->getTable('orocrm_magento_cart_address');
        $table->changeColumn('postal_code', ['length' => 255]);

        $table = $schema->getTable('orocrm_magento_order_address');
        $table->changeColumn('postal_code', ['length' => 255]);
    }
}
