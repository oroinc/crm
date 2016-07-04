<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Schema\v1_11;

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
        $table = $schema->getTable('orocrm_contactus_request');

        $workflowTables = [
            'oro_workflow_item',
            'oro_workflow_step',
        ];

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
