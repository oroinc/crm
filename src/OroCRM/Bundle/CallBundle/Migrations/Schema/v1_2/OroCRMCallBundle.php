<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCallBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_call');
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addIndex(['organization_id'], 'IDX_1FBD1A2432C8A3DE', []);
        $table->addForeignKeyConstraint($schema->getTable('oro_organization'), ['organization_id'],
            ['id'], ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
