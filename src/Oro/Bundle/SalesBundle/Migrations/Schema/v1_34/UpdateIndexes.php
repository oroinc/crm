<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_34;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateIndexes implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        $indexName = 'lead_created_idx';
        $indexColumns = ['createdAt', 'id'];
        if ($table->hasIndex($indexName) && $table->getIndex($indexName)->getColumns() !== $indexColumns) {
            $table->dropIndex($indexName);
            $table->addIndex($indexColumns, $indexName);
        }

        $table = $schema->getTable('orocrm_sales_opportunity');
        $indexName = 'opportunity_created_idx';
        $indexColumns = ['created_at', 'id'];
        if ($table->hasIndex($indexName) && $table->getIndex($indexName)->getColumns() !== $indexColumns) {
            $table->dropIndex($indexName);
            $table->addIndex($indexColumns, $indexName);
        }
    }
}
