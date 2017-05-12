<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_41_6;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RenameStepLabelForWorkflow implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $sql = 'UPDATE oro_workflow_step' .
            ' SET label = :label' .
            ' WHERE workflow_name = :workflow_name'.
            ' AND  name = :name';

        $queries->addQuery(
            new ParametrizedSqlMigrationQuery(
                $sql,
                [
                    'workflow_name' => 'b2c_flow_abandoned_shopping_cart',
                    'name' => 'converted_to_opportunity',
                    'label' => 'Converted to Opportunity'
                ]
            )
        );
    }
}
