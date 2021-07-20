<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_3_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class WorkflowItemEntityClass implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateWorkflowItemEntityClass($queries);
    }

    public static function updateWorkflowItemEntityClass(QueryBag $queries)
    {
        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_workflow_item',
            'entity_class',
            'OroCRM',
            'Oro'
        ));
    }
}
