<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_15;

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
        $table->addIndex(['last_name', 'first_name'], 'magecustomer_rev_name_idx');

        $table = $schema->getTable('orocrm_magento_product');
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addIndex(['data_channel_id'], 'IDX_5A172982BDC09B73', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_5A172982BDC09B73'
        );

        $table = $schema->getTable('orocrm_magento_cart_address');
        $table->addColumn('phone', 'string', ['notnull' => false, 'length' => 255]);
        $table = $schema->getTable('orocrm_magento_website');
        $table->addIndex(['website_name'], 'orocrm_magento_website_name_idx');
    }
}
