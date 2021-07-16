<?php

namespace Oro\Bundle\AnalyticsBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroAnalyticsBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createRFMMetricsCategoryTable($schema);
    }

    /**
     * Create oro_analytics_rfm_category table
     */
    protected function createRFMMetricsCategoryTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_analytics_rfm_category');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('category_type', 'string', ['length' => 16]);
        $table->addColumn('category_index', 'integer', []);
        $table->addColumn('min_value', 'float', ['notnull' => false]);
        $table->addColumn('max_value', 'float', ['notnull' => false]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['channel_id'], 'idx_channel', []);
        $table->addIndex(['owner_id'], 'idx_user_owner', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
