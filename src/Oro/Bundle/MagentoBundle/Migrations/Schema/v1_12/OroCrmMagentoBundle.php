<?php
namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_12;

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
        $cart = $schema->getTable('orocrm_magento_cart');
        $cart->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $cart->addIndex(['user_owner_id'], 'IDX_96661A809EB185F9', []);
        $cart->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_96661A809EB185F9'
        );

        $customer = $schema->getTable('orocrm_magento_customer');
        $customer->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $customer->addIndex(['user_owner_id'], 'IDX_2A61EE7D9EB185F9', []);
        $customer->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_2A61EE7D9EB185F9'
        );

        $order = $schema->getTable('orocrm_magento_order');
        $order->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $order->addIndex(['user_owner_id'], 'IDX_4D09F3059EB185F9', []);
        $order->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_4D09F3059EB185F9'
        );
    }
}
