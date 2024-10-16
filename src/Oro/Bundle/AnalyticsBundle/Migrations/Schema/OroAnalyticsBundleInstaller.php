<?php

namespace Oro\Bundle\AnalyticsBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroAnalyticsBundleInstaller implements Installation
{
    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_0';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createOrocrmAnalyticsRfmCategoryTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmAnalyticsRfmCategoryForeignKeys($schema);
    }

    /**
     * Create oro_analytics_rfm_category table
     */
    private function createOrocrmAnalyticsRfmCategoryTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_analytics_rfm_category');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('channel_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('category_type', 'string', ['length' => 16]);
        $table->addColumn('category_index', 'integer');
        $table->addColumn('min_value', 'float', ['notnull' => false]);
        $table->addColumn('max_value', 'float', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'idx_user_owner');
        $table->addIndex(['channel_id'], 'idx_channel');
    }

    /**
     * Add oro_analytics_rfm_category foreign keys.
     */
    private function addOrocrmAnalyticsRfmCategoryForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_analytics_rfm_category');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_channel'),
            ['channel_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
