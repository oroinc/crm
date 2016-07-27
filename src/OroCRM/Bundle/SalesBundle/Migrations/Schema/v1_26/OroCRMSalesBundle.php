<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_26;

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
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'workflow',
                'active_workflow',
                'opportunity_flow',
                'active_workflows'
            )
        );
    }
}
