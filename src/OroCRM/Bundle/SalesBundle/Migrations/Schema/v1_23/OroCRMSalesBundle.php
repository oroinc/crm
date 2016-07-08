<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_23;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMSalesBundle implements Migration
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
                'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'workflow',
                'active_workflows',
                ['opportunity_flow'],
                ['b2b_flow_sales']
            )
        );
    }
}
