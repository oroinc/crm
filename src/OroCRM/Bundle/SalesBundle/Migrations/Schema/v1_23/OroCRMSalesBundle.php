<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_23;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\WorkflowBundle\Migrations\Schema\v1_14\WorkflowActivationMigrationQuery;

class OroCRMSalesBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new UpdateWorkflowItemStepData());

        //change workflows
        $old = 'b2b_flow_sales';
        $new = 'opportunity_flow';
        $queries->addQuery(new WorkflowActivationMigrationQuery($old, false));
        $queries->addQuery(new WorkflowActivationMigrationQuery($new, false));
    }
}
