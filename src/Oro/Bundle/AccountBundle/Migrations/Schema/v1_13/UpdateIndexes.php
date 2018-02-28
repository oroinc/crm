<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema\v1_13;

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
        $table = $schema->getTable('orocrm_account');
        $indexName = 'account_name_idx';
        $indexColumns = ['name', 'id'];
        if ($table->hasIndex($indexName) && $table->getIndex($indexName)->getColumns() !== $indexColumns) {
            $table->dropIndex($indexName);
            $table->addIndex($indexColumns, $indexName);
        }
    }
}
