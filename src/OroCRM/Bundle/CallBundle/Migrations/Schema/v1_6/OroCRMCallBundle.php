<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;


class OroCRMCallBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_call');
        $table->addColumn('duration_string', 'string', ['length' => 255, 'notnull' => false]);
        $queries->addPostQuery(
            new SqlMigrationQuery(
                'UPDATE orocrm_call SET duration_string = CAST(duration AS CHAR(8))'
            )
        );
    }
}
