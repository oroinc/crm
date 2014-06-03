<?php
namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Schema\Table;
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
        $this->addOwnerField($schema, $cart);

        $customer = $schema->getTable('orocrm_magento_customer');
        $this->addOwnerField($schema, $customer);

        $order = $schema->getTable('orocrm_magento_order');
        $this->addOwnerField($schema, $order);
    }

    /**
     * @param Schema $schema
     * @param Table  $table
     */
    protected function addOwnerField(Schema $schema, Table $table)
    {
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['user_owner_id'], 'IDX_2A61EE7D9EB185F9', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_2A61EE7D9EB185F9'
        );
    }
}
