<?php

namespace Oro\Bundle\ContactUsBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SecurityBundle\Migrations\Schema\SetOwnershipTypeQuery;

class OroContactUsBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::addOwner($schema);
        self::setOwnership($queries);
    }

    /**
     * Adds owner_id field
     */
    public static function addOwner(Schema $schema)
    {
        $table = $schema->getTable('orocrm_contactus_request');

        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addIndex(['owner_id'], 'IDX_342872E87E3C61F9', []);

        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Set ownership type for Contact Request entity to Organization
     */
    public function setOwnership(QueryBag $queries)
    {
        $queries->addQuery(
            new SetOwnershipTypeQuery('Oro\Bundle\ContactUsBundle\Entity\ContactRequest')
        );
    }
}
