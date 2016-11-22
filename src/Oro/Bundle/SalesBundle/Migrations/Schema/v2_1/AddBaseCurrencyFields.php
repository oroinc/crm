<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddBaseCurrencyFields implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queryBag)
    {
        self::addBaseCurrencyFields($schema);
    }

    /**
     * @param Schema $schema
     */
    public static function addBaseCurrencyFields(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_opportunity');

        //Add columns for new type
        $table->addColumn(
            'base_budget_amount_value',
            'money',
            ['notnull' => false, 'comment' => '(DC2Type:money)']
        );
        $table->addColumn(
            'base_close_revenue_value',
            'money',
            ['notnull' => false, 'comment' => '(DC2Type:money)']
        );
    }
}
