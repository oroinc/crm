<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class AddCIIndex implements Migration, DatabasePlatformAwareInterface
{
    use DatabasePlatformAwareTrait;

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        if ($this->platform instanceof PostgreSqlPlatform) {
            $queries->addPostQuery(new SqlMigrationQuery(
                'CREATE INDEX account_name_expr_idx ON orocrm_account (LOWER(name))'
            ));
        }
    }
}
