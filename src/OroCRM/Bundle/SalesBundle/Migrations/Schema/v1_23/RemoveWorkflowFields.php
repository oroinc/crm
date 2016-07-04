<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_23;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveWorkflowFields implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        //workflow now has no direct relations
        $leadTable = $schema->getTable('orocrm_sales_lead');
        $leadTable
            ->dropColumn('workflow_step_id')
            ->dropColumn('workflow_item_id');

        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');
        $opportunityTable
            ->dropColumn('workflow_step_id')
            ->dropColumn('workflow_item_id');

        $salesFunnelTable = $schema->getTable('orocrm_sales_funnel');
        $salesFunnelTable
            ->dropColumn('workflow_step_id')
            ->dropColumn('workflow_item_id');
    }
}
