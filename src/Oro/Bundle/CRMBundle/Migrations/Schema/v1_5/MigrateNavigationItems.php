<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateNavigationItems implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if (!$schema->hasTable('oro_navigation_item')) {
            return;
        }

        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_navigation_item',
            'title',
            'orocrm',
            'oro'
        ));
        $queries->addQuery(new UpdateTableFieldQuery(
            'oro_navigation_history',
            'title',
            'orocrm',
            'oro'
        ));
    }
}
