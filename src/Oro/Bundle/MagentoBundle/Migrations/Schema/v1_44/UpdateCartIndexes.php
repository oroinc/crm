<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_44;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateCartIndexes implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_cart');
        if (!$table->hasIndex('magecart_payment_details_idx')) {
            $table->addIndex(['payment_details'], 'magecart_payment_details_idx', []);
        }
        if (!$table->hasIndex('status_name_items_qty_idx')) {
            $table->addIndex(['status_name', 'items_qty'], 'status_name_items_qty_idx', []);
        }
    }
}
