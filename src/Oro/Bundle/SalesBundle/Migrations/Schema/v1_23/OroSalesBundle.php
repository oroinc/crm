<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_23;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new UpdateWorkflowItemStepData());

        // applies only if config has old active workflow
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'Oro\Bundle\SalesBundle\Entity\Opportunity',
                'workflow',
                'active_workflow',
                'opportunity_flow',
                'b2b_flow_sales'
            )
        );
    }
}
