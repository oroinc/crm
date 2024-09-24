<?php

namespace Oro\Bundle\TestFrameworkCRMBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;
use Oro\Bundle\TestFrameworkCRMBundle\Migrations\Data\ORM\FillContactTestMultiEnum;

/**
 * IMPORTANT!!!
 * Please, do not create new migrations in `Migrations/Schema` folder!
 * Add new schema migrations to this installer instead.
 */
class OroTestFrameworkBundleInstaller implements
    Installation,
    CustomerExtensionAwareInterface,
    ExtendExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;
    use CustomerExtensionTrait;

    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_0';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->addTestCustomerTargetTables($schema);
        $this->addCustomerAssociations($schema);
        $this->addTestEnumFieldToContact($schema);
    }

    private function addTestCustomerTargetTables(Schema $schema): void
    {
        $table = $schema->createTable('test_customer1');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('test_customer2');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);

        $table = $schema->createTable('test_customer_with_contact_info');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('email', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('phone', 'string', ['length' => 255, 'notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    private function addCustomerAssociations(Schema $schema): void
    {
        $this->customerExtension->addCustomerAssociation($schema, 'test_customer1');
        $this->customerExtension->addCustomerAssociation($schema, 'test_customer2');
    }

    private function addTestEnumFieldToContact(Schema $schema): void
    {
        $contactTable = $schema->getTable('orocrm_contact');
        $this->extendExtension->addEnumField(
            $schema,
            $contactTable,
            FillContactTestMultiEnum::CONTACT_FIELD_TEST_ENUM_CODE,
            ExtendHelper::buildEnumCode(FillContactTestMultiEnum::CONTACT_FIELD_TEST_ENUM_CODE),
            true,
            false,
            [
                'importexport' => ['excluded' => true]
            ]
        );
    }
}
