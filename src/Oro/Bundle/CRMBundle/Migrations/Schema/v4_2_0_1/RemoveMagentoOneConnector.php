<?php
declare(strict_types=1);

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v4_2_0_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\CRMBundle\Migration\CleanupMagentoOneConnectorEntities;
use Oro\Bundle\CRMBundle\Migration\CleanupMagentoOneConnectorEntityConfigsQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\WorkflowBundle\Migration\RemoveWorkflowDefinitionsForRelatedEntityQuery;

class RemoveMagentoOneConnector implements Migration
{
    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException if fails to alter database schema
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if (\class_exists('Oro\Bundle\MagentoBundle\OroMagentoBundle', false)) {
            return;
        }

        foreach (CleanupMagentoOneConnectorEntities::getQueries(true) as $query) {
            $queries->addQuery($query);
        }

        foreach (CleanupMagentoOneConnectorEntityConfigsQuery::ENTITY_CLASSES as $className) {
            $queries->addQuery(
                new ParametrizedSqlMigrationQuery(
                    'DELETE FROM orocrm_channel_entity_name WHERE name = :name',
                    ['name' => $className],
                    ['name' => Types::STRING]
                )
            );

            $queries->addQuery(
                new ParametrizedSqlMigrationQuery(
                    'DELETE FROM oro_process_definition WHERE related_entity = :related_entity',
                    ['related_entity' => $className],
                    ['related_entity' => Types::STRING]
                )
            );

            $queries->addQuery(new RemoveWorkflowDefinitionsForRelatedEntityQuery($className));
        }

        $table = $schema->getTable('oro_integration_transport');
        $table->dropColumn('wsdl_url'); // before 1_49 version of MagentoBundle migration
        $table->dropColumn('api_url');  // since 1_49 version of MagentoBundle migration
        $table->dropColumn('api_user');
        $table->dropColumn('api_key');
        $table->dropColumn('sync_start_date');
        $table->dropColumn('sync_range');
        $table->dropColumn('website_id');
        $table->dropColumn('websites');
        $table->dropColumn('is_extension_installed');
        $table->dropColumn('is_wsi_mode');
        $table->dropColumn('admin_url');
        $table->dropColumn('initial_sync_start_date');
        $table->dropColumn('extension_version');
        $table->dropColumn('magento_version');
        $table->dropColumn('guest_customer_sync');
        $table->dropColumn('mage_newsl_subscr_synced_to_id');
        $table->dropColumn('api_token');
        $table->dropColumn('is_display_order_notes');
        $table->dropColumn('shared_guest_email_list');
    }
}
