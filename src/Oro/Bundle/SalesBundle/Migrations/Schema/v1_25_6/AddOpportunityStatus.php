<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_25_6;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddOpportunityStatus implements Migration
{

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateOpportunityStatuses($queries);
    }

    /**
     * @param QueryBag $queries
     */
    public static function updateOpportunityStatuses(QueryBag $queries)
    {
        $query = 'UPDATE oro_enum_opportunity_status SET name = :name WHERE id = :id';
        $updateStatusQuery = new ParametrizedSqlMigrationQuery();
        $updateStatusQuery->addSql(
            $query,
            ['id' => 'in_progress', 'name' => 'Open'],
            [
                'id' => Type::STRING,
                'name' => Type::STRING,
            ]
        );
        $queries->addQuery($updateStatusQuery);
    }
}
