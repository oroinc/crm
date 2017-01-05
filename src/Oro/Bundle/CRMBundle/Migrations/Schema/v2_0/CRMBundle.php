<?php

namespace Oro\Bundle\CRMBundle\Migrations\Schema\v2_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CRMBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $updateQueries = [];
        $updateQueries[] = <<<'SQL'
UPDATE oro_grid_view_user_rel t
    SET t.grid_name = CONCAT(:prefix, SUBSTRING(t.grid_name, :replaced_length))
    WHERE t.grid_name LIKE :pattern
SQL;
        $updateQueries[] = <<<'SQL'
UPDATE oro_grid_view t
    SET t.gridName = CONCAT(:prefix, SUBSTRING(t.gridName, :replaced_length))
    WHERE t.gridName LIKE :pattern
SQL;

        foreach ($updateQueries as $query) {
            $queries->addQuery(
                new ParametrizedSqlMigrationQuery(
                    $query,
                    ['pattern' => 'orocrm%', 'prefix' => 'oro', 'replaced_length'=> 7]
                )
            );
        }
    }
}
