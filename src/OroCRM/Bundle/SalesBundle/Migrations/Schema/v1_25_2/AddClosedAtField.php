<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_25_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use OroCRM\Bundle\SalesBundle\Migration\FillClosedAtField;

class AddClosedAtField implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        if (!$table->hasColumn('closed_at')) {
            $table->addColumn('closed_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
            $queries->addPostQuery(new FillClosedAtField());
        }
    }
}
