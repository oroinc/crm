<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_40;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Creates index by updatedAt column for orocrm_sales_lead table.
 */
class CreateLeadUpdatedAtIndex implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_lead');
        if (!$table->hasIndex('lead_updated_idx')) {
            $table->addIndex(['updatedat'], 'lead_updated_idx');
        }
    }
}
