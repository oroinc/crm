<?php

namespace Oro\Bundle\CaseBundle\Migrations\Schema\v1_10;

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
        $table = $schema->getTable('orocrm_case');
        $indexName = 'case_reported_at_idx';
        $indexColumns = ['reportedAt', 'id'];
        if ($table->hasIndex($indexName)) {
            if ($table->getIndex($indexName)->getColumns() !== $indexColumns) {
                $table->dropIndex($indexName);
                $table->addIndex($indexColumns, $indexName);
            }
        } else {
            $table->addIndex($indexColumns, $indexName);
        }
    }
}
