<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;

class AddCustomersTable implements Migration, CustomerExtensionAwareInterface
{
    use CustomerExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addCustomersTable($schema, $this->customerExtension);
        self::addCustomersTableForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     * @param CustomerExtension $customerExtension
     */
    public static function addCustomersTable(Schema $schema, CustomerExtension $customerExtension)
    {
        $table = $schema->createTable('orocrm_sales_customer');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('account_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $customerExtension->addCustomerAssociation($schema, 'orocrm_account');
    }

    /**
     * @param Schema $schema
     */
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
