<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_41_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveWorkflowFields implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        //workflow now has no direct relations

        $magentoTables = [
            'orocrm_magento_order',
            'orocrm_magento_cart',
        ];
        $workflowTables = [
            'oro_workflow_item',
            'oro_workflow_step',
        ];

        foreach ($magentoTables as $magentoTable) {
            if ($schema->hasTable($magentoTable)) {

                $table = $schema->getTable($magentoTable);

                foreach ($table->getForeignKeys() as $foreignKey) {
                    if (in_array($foreignKey->getForeignTableName(), $workflowTables, true)) {

                        $table->removeForeignKey($foreignKey->getName());
                        
                        foreach ($foreignKey->getLocalColumns() as $column) {
                            $table->dropColumn($column);
                        }
                    }
                }
            }
        }
    }
}
