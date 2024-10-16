<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_44;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Drop opportunities_by_status with status serialized field index.
 */
class DropOpportunitiesByStatusIndex implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $indexName = 'opportunities_by_status_idx';
        if ($table->hasIndex($indexName)) {
            $table->dropIndex($indexName);
        }
    }
}
