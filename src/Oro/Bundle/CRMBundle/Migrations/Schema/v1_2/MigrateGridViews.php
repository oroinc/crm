<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateGridViews implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateGridViews($queries);
    }

    public static function updateGridViews(QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateTableFieldQuery(
                'oro_grid_view',
                'gridname',
                'orocrm',
                'oro'
            )
        );

        $queries->addQuery(
            new UpdateTableFieldQuery(
                'oro_grid_view_user_rel',
                'grid_name',
                'orocrm',
                'oro'
            )
        );
    }
}
