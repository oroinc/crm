<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateRelations implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateWorkFlow($schema, $queries);
    }

    /**
     * Change name OroCRM to Oro for workflows tables
     */
    public static function updateWorkFlow(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('oro_workflow_entity_acl')) {
            $queries->addQuery(new UpdateTableFieldQuery(
                'oro_workflow_entity_acl',
                'entity_class',
                'OroCRM',
                'Oro'
            ));
        }

        if ($schema->hasTable('oro_workflow_entity_acl_ident')) {
            $queries->addQuery(new UpdateTableFieldQuery(
                'oro_workflow_entity_acl_ident',
                'entity_class',
                'OroCRM',
                'Oro'
            ));
        }

        if ($schema->hasTable('oro_workflow_restriction')) {
            $queries->addQuery(new UpdateTableFieldQuery(
                'oro_workflow_restriction',
                'entity_class',
                'OroCRM',
                'Oro'
            ));
        }

        if ($schema->hasTable('oro_workflow_definition')) {
            $queries->addQuery(new UpdateTableFieldQuery(
                'oro_workflow_definition',
                'related_entity',
                'OroCRM',
                'Oro',
                'name'
            ));
        }
    }
}
