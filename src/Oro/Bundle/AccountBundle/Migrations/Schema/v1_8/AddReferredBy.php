<?php

namespace Oro\Bundle\AccountBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddReferredBy implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_account');

        $table->addColumn('referred_by_id', 'integer', ['notnull' => false]);
        $table->addIndex(['referred_by_id'], 'IDX_7166D371758C8114', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_account'),
            ['referred_by_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
