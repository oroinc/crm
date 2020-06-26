<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_35;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RemoveOldData implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $sql = <<<EOF
DELETE FROM orocrm_channel_entity_name
WHERE name = :name
EOF;

        $removeDataQuery = new ParametrizedSqlMigrationQuery();
        $removeDataQuery->addSql(
            $sql,
            ['name' => 'Oro\\Bundle\\SalesBundle\\Entity\\Lead'],
            ['name' => Types::STRING]
        );
        $queries->addPostQuery($removeDataQuery);

        $removeDataQuery = new ParametrizedSqlMigrationQuery();
        $removeDataQuery->addSql(
            $sql,
            ['name' => 'Oro\\Bundle\\SalesBundle\\Entity\\Opportunity'],
            ['name' => Types::STRING]
        );
        $queries->addPostQuery($removeDataQuery);
    }
}
