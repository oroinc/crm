<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Schema\v1_2;

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
        $this->createOrocrmChannelLtimeAvgAggrTable($schema);
        $this->addOrocrmChannelLtimeAvgAggrForeignKeys($schema);
    }

    /**
     * Create oro_channel_ltime_avg_aggr table
     *
     * @param Schema $schema
     */
    protected function createOrocrmChannelLtimeAvgAggrTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_channel_ltime_avg_aggr');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('data_channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('amount', 'money', ['precision' => 19, 'scale' => 4, 'comment' => '(DC2Type:money)']);
        $table->addColumn('aggregation_date', 'datetime', []);
        $table->addColumn('month', 'smallint', ['unsigned' => true]);
        $table->addColumn('quarter', 'smallint', ['unsigned' => true]);
        $table->addColumn('year', 'smallint', ['unsigned' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['data_channel_id'], 'IDX_EBDA8490BDC09B73', []);
    }

    /**
     * Add oro_channel_ltime_avg_aggr foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmChannelLtimeAvgAggrForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_channel_ltime_avg_aggr');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['data_channel_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_EBDA8490BDC09B73'
        );
    }
}
