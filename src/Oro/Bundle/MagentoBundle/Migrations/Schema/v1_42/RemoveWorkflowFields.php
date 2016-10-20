<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_42;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\WorkflowBundle\Migrations\Schema\RemoveWorkflowFieldsTrait;

class RemoveWorkflowFields implements Migration
{
    use RemoveWorkflowFieldsTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        //workflow now has no direct relations
        $magentoTables = [
            'oro_magento_order',
            'oro_magento_cart',
        ];

        foreach ($magentoTables as $magentoTable) {
            if ($schema->hasTable($magentoTable)) {
                $this->removeWorkflowFields($schema->getTable($magentoTable));
            }
        }
    }
}
