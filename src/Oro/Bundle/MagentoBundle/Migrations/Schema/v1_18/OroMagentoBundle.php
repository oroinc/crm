<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_18;

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
        $this->modifyOrocrmMagentoCartTable($schema);
        $this->modifyOrocrmMagentoOrderTable($schema);
        $this->modifyOrocrmMagentoCustomerTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function modifyOrocrmMagentoOrderTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_order');
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_4D09F305BDC09B73', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_4D09F305BDC09B73'
        );
    }

    /**
     * @param Schema $schema
     */
    protected function modifyOrocrmMagentoCustomerTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_customer');
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_2A61EE7DBDC09B73', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_2A61EE7DBDC09B73'
        );
    }

    /**
     * @param Schema $schema
     */
    protected function modifyOrocrmMagentoCartTable(Schema $schema)
    {
        $table = $schema->getTable('orocrm_magento_cart');
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_96661A80BDC09B73', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_96661A80BDC09B73'
        );
    }
}
