<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_14;

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
        $table = $schema->getTable('orocrm_contactus_request');
        $indexName = 'request_create_idx';
        $indexColumns = ['created_at', 'id'];
        if ($table->hasIndex($indexName) && $table->getIndex($indexName)->getColumns() !== $indexColumns) {
            $table->dropIndex($indexName);
            $table->addIndex($indexColumns, $indexName);
        }
    }
}
