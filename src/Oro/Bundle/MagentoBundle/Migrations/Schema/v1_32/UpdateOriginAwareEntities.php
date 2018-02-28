<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_32;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateOriginAwareEntities implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_magento_order_address');
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('origin_id', 'integer', ['notnull' => false, 'precision' => 0, 'unsigned' => true]);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\OrderAddress', 'street');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\OrderAddress', 'city');
        $this->removeIdentity($queries, 'Oro\Bundle\MagentoBundle\Entity\OrderAddress', 'postalCode');

        $table = $schema->getTable('orocrm_magento_cart_item');
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );

        $table = $schema->getTable('orocrm_magento_order_items');
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_channel'),
            ['channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL']
        );
    }

    /**
     * @param QueryBag $queries
     * @param string $class
     * @param string $field
     */
    protected function removeIdentity(QueryBag $queries, $class, $field)
    {
        $queries->addQuery(new UpdateEntityConfigFieldValueQuery($class, $field, 'importexport', 'identity', false));
    }
}
