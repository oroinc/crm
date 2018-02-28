<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_36;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCouponCodeToOrder implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_order');
        $table->addColumn('coupon_code', 'string', ['notnull' => false, 'length' => 255, 'precision' => 0]);
    }
}
