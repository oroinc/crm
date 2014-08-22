<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMSalesBundle implements Migration, OrderedMigrationInterface
{
    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $customerTable = $schema->createTable('orocrm_sales_customer');
        $customerTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $customerTable->addColumn('account_id', 'integer', ['notnull' => false]);

        $customerTable->setPrimaryKey(['id']);
        $customerTable->addIndex(['account_id'], 'IDX_94CC12929B6B5FBA', []);

        $customerTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null],
            'FK_9C6CFD79B6B5FBA'
        );
    }
}
