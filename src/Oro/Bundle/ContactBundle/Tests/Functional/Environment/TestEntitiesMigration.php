<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Environment;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class TestEntitiesMigration implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->addCustomFieldsToContactAddress($schema);
    }

    private function addCustomFieldsToContactAddress(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_contact_address');
        if ($table->hasColumn('customField1')) {
            return;
        }

        $table->addColumn(
            'customField1',
            'string',
            [
                'length'        => 255,
                OroOptions::KEY => [
                    'extend'       => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'entity'       => ['label' => 'extend.entity.test.contactaddress.custom_field1.label'],
                    'importexport' => ['excluded' => true]
                ]
            ]
        );
        $table->addColumn(
            'custom_field_2',
            'string',
            [
                'length'        => 255,
                OroOptions::KEY => [
                    'extend'       => ['owner' => ExtendScope::OWNER_CUSTOM],
                    'entity'       => ['label' => 'extend.entity.test.contactaddress.custom_field_2.label'],
                    'importexport' => ['excluded' => true]
                ]
            ]
        );
    }
}
