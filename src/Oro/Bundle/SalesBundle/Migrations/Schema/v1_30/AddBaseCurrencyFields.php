<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_30;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddBaseCurrencyFields implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queryBag)
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

    public function getOrder()
    {
        return 1;
    }
}
