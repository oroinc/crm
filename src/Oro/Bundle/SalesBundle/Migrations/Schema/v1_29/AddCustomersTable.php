<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_29;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCustomersTable implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addCustomersTable($schema);
        self::addCustomersTableForeignKeys($schema);

        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');
        $opportunityTable->addColumn('customer_association_id', 'integer', ['notnull' => false]);
        $opportunityTable->addIndex(['customer_association_id'], 'IDX_C0FE4AAC76D4FC6F', []);
        $opportunityTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['customer_association_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $leadTable = $schema->getTable('orocrm_sales_lead');
        $leadTable->addColumn('customer_association_id', 'integer', ['notnull' => false]);
        $leadTable->addIndex(['customer_association_id'], 'IDX_73DB463376D4FC6F', []);
        $leadTable->addForeignKeyConstraint(
            $schema->getTable('orocrm_sales_customer'),
            ['customer_association_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    public static function addCustomersTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_sales_customer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('account_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
    }

    public static function addCustomersTableForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_sales_customer');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['account_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
