<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_33;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCartItemRemovedField implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_cart_item');
        $table->addColumn('is_removed', 'boolean', ['notnull' => true, 'default' => false]);
    }
}
