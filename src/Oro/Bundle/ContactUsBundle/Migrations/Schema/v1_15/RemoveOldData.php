<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_15;

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
            ['name' => 'Oro\\Bundle\\ContactUsBundle\\Entity\\ContactRequest'],
            ['name' => Types::STRING]
        );
        $queries->addPostQuery($removeDataQuery);
    }
}
