<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_42;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveTableQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Removes all data associated with deprecated feature "Sales Process".
 */
class RemoveSalesFunnelData implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('orocrm_sales_funnel')) {
            $schema->dropTable('orocrm_sales_funnel');
        }

        $this->removeWorkflowData($queries);
        $this->removeAclClassesData($queries);
        $this->removeConfigData($queries);
        $this->removeDashboardWidgetData($queries);

        $queries->addPostQuery(new RemoveTableQuery('Oro\Bundle\SalesBundle\Entity\SalesFunnel'));
    }

    private function removeWorkflowData(QueryBag $queries): void
    {
        $queries->addQuery(new ParametrizedSqlMigrationQuery(
            'DELETE FROM oro_workflow_definition WHERE name = :name',
            ['name' => 'b2b_flow_sales_funnel']
        ));
    }

    private function removeAclClassesData(QueryBag $queries): void
    {
        $queries->addQuery(new ParametrizedSqlMigrationQuery(
            'DELETE FROM acl_classes WHERE class_type IN (:class_types)',
            [
                'class_types' => [
                    'b2b_flow_sales_funnel',
                    'Oro\Bundle\SalesBundle\Entity\SalesFunnel'
                ]
            ],
            ['class_types' => Connection::PARAM_STR_ARRAY]
        ));
    }

    private function removeDashboardWidgetData(QueryBag $queries): void
    {
        $queries->addQuery(new ParametrizedSqlMigrationQuery(
            'DELETE FROM oro_dashboard_widget WHERE name = :name',
            ['name' => 'my_sales_flow_b2b_chart']
        ));
    }

    private function removeConfigData(QueryBag $queries): void
    {
        $queries->addQuery(new ParametrizedSqlMigrationQuery(
            'DELETE FROM oro_config_value WHERE name = :name',
            ['name' => 'salesfunnel_feature_enabled']
        ));
    }
}
