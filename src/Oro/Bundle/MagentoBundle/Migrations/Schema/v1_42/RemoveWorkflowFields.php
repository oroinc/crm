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
            'orocrm_magento_order' => 'Oro\Bundle\MagentoBundle\Entity\Order',
            'orocrm_magento_cart' => 'Oro\Bundle\MagentoBundle\Entity\Cart'
        ];

        foreach ($magentoTables as $magentoTable => $entityClass) {
            if ($schema->hasTable($magentoTable)) {
                $this->removeWorkflowFields($schema->getTable($magentoTable));
                $this->removeConfigsForWorkflowFields($entityClass, $queries);
            }
        }
    }
}
