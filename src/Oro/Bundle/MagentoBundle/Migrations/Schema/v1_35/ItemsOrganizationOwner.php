<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_35;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class ItemsOrganizationOwner implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_order_items');
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_id'], 'IDX_3135EFF67E3C61F9', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $queries->addPostQuery(
            'UPDATE orocrm_magento_order_items itm '.
            'SET owner_id = (SELECT organization_id FROM orocrm_magento_order WHERE itm.order_id = id)'
        );

        $table = $schema->getTable('orocrm_magento_cart_item');
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_id'], 'IDX_A73DC8627E3C61F9', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $queries->addPostQuery(
            'UPDATE orocrm_magento_cart_item itm '.
            'SET owner_id = (SELECT organization_id FROM orocrm_magento_cart WHERE itm.cart_id = id)'
        );
    }
}
