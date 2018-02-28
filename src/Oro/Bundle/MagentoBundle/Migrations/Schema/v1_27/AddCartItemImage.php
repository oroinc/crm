<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_27;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCartItemImage implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_cart_item');
        $table->addColumn('product_image_url', 'text', ['notnull' => false]);
        $table->addColumn('product_url', 'text', ['notnull' => false]);
    }
}
