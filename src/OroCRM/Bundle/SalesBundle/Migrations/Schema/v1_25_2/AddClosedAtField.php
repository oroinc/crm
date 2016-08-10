<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_25_2;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddClosedAtField implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        if (!$table->hasColumn('closed_at')) {
            $table->addColumn('closed_at', 'datetime', ['notnull' => false]);
            $queries->addPostQuery(new FillClosedAtField());
        }
    }
}
