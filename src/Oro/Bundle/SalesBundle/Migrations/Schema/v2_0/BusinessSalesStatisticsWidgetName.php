<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;

class BusinessSalesStatisticsWidgetName implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new ParametrizedSqlMigrationQuery(
                'UPDATE oro_dashboard_widget SET name = :newName WHERE name = :oldName',
                ['oldName' => 'business_sales_channel_statistics', 'newName' => 'opportunity_statistics'],
                ['oldName' => Type::STRING, 'newName' => Type::STRING]
            )
        );
    }
}
