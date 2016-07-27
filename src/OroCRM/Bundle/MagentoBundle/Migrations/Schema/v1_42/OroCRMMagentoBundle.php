<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_42;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\WorkflowBundle\Migrations\Schema\v1_14\WorkflowActivationMigrationQuery;

class OroCRMMagentoBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $cartWorkflow = 'b2c_flow_abandoned_shopping_cart';
        $orderWorkflow = 'b2c_flow_order_follow_up';
        $queries->addQuery(new WorkflowActivationMigrationQuery($cartWorkflow, true));
        $queries->addQuery(new WorkflowActivationMigrationQuery($orderWorkflow, true));
    }
}
