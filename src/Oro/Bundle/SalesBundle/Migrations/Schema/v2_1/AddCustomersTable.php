<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomersTable implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addCustomersTable($schema);
        self::addCustomersTableForeignKeys($schema);
    }

    public static function addCustomersTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_customer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    public static function addCustomersTableForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
