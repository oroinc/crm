<?php

namespace Oro\Bundle\ContactBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroContactBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        // cascade delete for owner fields
        $this->setOwnerCascadeDelete($schema, $schema->getTable('orocrm_contact_email'), 'FK_335A28C37E3C61F9');
        $this->setOwnerCascadeDelete($schema, $schema->getTable('orocrm_contact_address'), 'FK_CACC16DB7E3C61F9');
        $this->setOwnerCascadeDelete($schema, $schema->getTable('orocrm_contact_phone'), 'FK_9087C36A7E3C61F9');

        // cascade delete for many-to-many address to type
        $addressToTypeTable = $schema->getTable('orocrm_contact_adr_to_adr_type');
        foreach ($addressToTypeTable->getForeignKeys() as $foreignKey) {
            if ($foreignKey->getLocalColumns() == ['contact_address_id']) {
                $addressToTypeTable->removeForeignKey($foreignKey->getName());
                $addressToTypeTable->addForeignKeyConstraint(
                    $schema->getTable('orocrm_contact_address'),
                    ['contact_address_id'],
                    ['id'],
                    ['onDelete' => 'CASCADE', 'onUpdate' => null],
                    'FK_690C2E9F320EF6E2'
                );
                break;
            }
        }
    }

    /**
     * @param Schema $schema
     * @param Table $table
     * @param string $keyName
     */
    protected function setOwnerCascadeDelete(Schema $schema, Table $table, $keyName)
    {
        foreach ($table->getForeignKeys() as $foreignKey) {
            if ($foreignKey->getLocalColumns() == ['owner_id']) {
                $table->removeForeignKey($foreignKey->getName());
                $table->addForeignKeyConstraint(
                    $schema->getTable('orocrm_contact'),
                    ['owner_id'],
                    ['id'],
                    ['onDelete' => 'CASCADE', 'onUpdate' => null],
                    $keyName
                );
                break;
            }
        }
    }
}
