<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMContactBundle implements migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addOrganization($schema);
    }

    /**
     * Adds organization_id field
     *
     * @param Schema $schema
     */
    public static function addOrganization(Schema $schema)
    {
        $table = $schema->getTable('orocrm_contact');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['organization_id'], 'IDX_403263ED32C8A3DE', []);
        $table->addForeignKeyConstraint($schema->getTable('oro_organization'), ['organization_id'],
            ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );

        $table = $schema->getTable('orocrm_contact_group');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['organization_id'], 'IDX_B908107232C8A3DE', []);
        $table->addForeignKeyConstraint($schema->getTable('oro_organization'), ['organization_id'],
            ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
