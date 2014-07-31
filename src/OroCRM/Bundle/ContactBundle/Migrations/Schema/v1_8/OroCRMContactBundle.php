<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactBundle implements migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery('ALTER TABLE orocrm_contact_to_contact_grp DROP FOREIGN KEY FK_A748EE19647145D0;');

        $table = $schema->getTable('orocrm_contact_to_contact_grp');
        $table->getColumn('contact_group_id')->setType(Type::getType('integer'));
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_contact_group'),
            ['contact_group_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null],
            'FK_A748EE19647145D0'
        );

        $table = $schema->getTable('orocrm_contact_group');
        $table->getColumn('id')->setType(Type::getType('integer'));
    }
}
