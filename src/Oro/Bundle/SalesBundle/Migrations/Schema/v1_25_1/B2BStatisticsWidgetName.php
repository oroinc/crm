<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_25_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class B2BStatisticsWidgetName implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new ParametrizedSqlMigrationQuery(
                'UPDATE oro_dashboard_widget SET name = :newName WHERE name = :oldName',
                ['oldName' => 'b2b_statistics_widget', 'newName' => 'business_sales_channel_statistics'],
                ['oldName' => Types::STRING, 'newName' => Types::STRING]
            )
        );
    }
}
