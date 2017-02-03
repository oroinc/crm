<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtension;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;

class AddCustomerAssociation implements Migration, CustomerExtensionAwareInterface
{
    use CustomerExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addTestCustomerTargetTables($schema);
        self::addCustomerAssociations($schema, $this->customerExtension);
    }

    /**
     * @param Schema $schema
     */
    public static function addTestCustomerTargetTables(Schema $schema)
    {
        $table = $schema->createTable('test_customer1');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('test_customer2');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema            $schema
     * @param CustomerExtension $customerExtension
     */
    public static function addCustomerAssociations(Schema $schema, CustomerExtension $customerExtension)
    {
        $customerExtension->addCustomerAssociation($schema, 'test_customer1');
        $customerExtension->addCustomerAssociation($schema, 'test_customer2');
    }
}
