<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_46;

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
        $table = $schema->getTable('orocrm_magento_cart');
        $indexName = 'magecart_updated_idx';
        $indexColumns = ['updatedAt', 'id'];
        if ($table->hasIndex($indexName) && $table->getIndex($indexName)->getColumns() !== $indexColumns) {
            $table->dropIndex($indexName);
            $table->addIndex($indexColumns, $indexName);
        }

        $table = $schema->getTable('orocrm_magento_order');
        $indexName = 'mageorder_created_idx';
        $indexColumns = ['created_at', 'id'];
        if ($table->hasIndex($indexName) && $table->getIndex($indexName)->getColumns() !== $indexColumns) {
            $table->dropIndex($indexName);
            $table->addIndex($indexColumns, $indexName);
        }

        $table = $schema->getTable('orocrm_magento_customer');
        $indexName = 'magecustomer_rev_name_idx';
        $indexColumns = ['last_name', 'first_name', 'id'];
        if ($table->hasIndex($indexName) && $table->getIndex($indexName)->getColumns() !== $indexColumns) {
            $table->dropIndex($indexName);
            $table->addIndex($indexColumns, $indexName);
        }
    }
}
