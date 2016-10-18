<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroChannelBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createOrocrmChannelLifetimeHistTable($schema);
        $this->addOrocrmChannelLifetimeHistForeignKeys($schema);
        $this->addChannelIndexes($schema);
    }

    /**
     * Create oro_channel_lifetime_hist table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelLifetimeHistTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_lifetime_hist');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('status', 'boolean', ['notnull' => true]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn(
            'amount',
            'money',
            ['notnull' => true, 'precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn('created_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['data_channel_id'], 'IDX_2B156554BDC09B73', []);
        $table->addIndex(['account_id'], 'IDX_2B1565549B6B5FBA', []);
        $table->addIndex(['account_id', 'data_channel_id', 'status'], 'orocrm_chl_ltv_hist_idx');
        $table->addIndex(['status'], 'orocrm_chl_ltv_hist_status_idx');
    }

    /**
     * Add oro_channel_lifetime_hist foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelLifetimeHistForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_lifetime_hist');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_2B1565549B6B5FBA'
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_2B156554BDC09B73'
        );
    }

    /**
     * @param Schema $schema
     */
    protected function addChannelIndexes(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel');
        $table->addIndex(['name'], 'crm_channel_name_idx', []);
        $table->addIndex(['status'], 'crm_channel_status_idx', []);
        $table->addIndex(['channel_type'], 'crm_channel_channel_type_idx', []);
    }
}
