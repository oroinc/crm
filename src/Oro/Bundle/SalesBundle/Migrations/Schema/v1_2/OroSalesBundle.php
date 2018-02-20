<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroSalesBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');
        $table->getColumn('probability')->setType(Type::getType('percent'));
        $table->getColumn('budget_amount')->setType(Type::getType('money'));
        $table->getColumn('close_revenue')->setType(Type::getType('money'));
    }
}
