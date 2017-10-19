<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v1_50_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveMagentoTransportResyncTrigger implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('oro_process_definition')) {
            $queries->addQuery(new ParametrizedSqlMigrationQuery(
                'DELETE FROM oro_process_definition WHERE name = :name',
                ['name' => 'magento_schedule_integration']
            ));
            $queries->addQuery(new ParametrizedSqlMigrationQuery(
                'DELETE FROM oro_process_trigger WHERE definition_name = :name',
                ['name' => 'magento_schedule_integration']
            ));
        }
    }
}
