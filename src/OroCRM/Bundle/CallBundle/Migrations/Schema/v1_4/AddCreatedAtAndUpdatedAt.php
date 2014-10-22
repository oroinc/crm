<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCreatedAtAndUpdatedAt implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('orocrm_call');
        $table->addColumn('createdAt', 'datetime', ['notnull' => false]);
        $table->addColumn('updatedAt', 'datetime', ['notnull' => false]);
    }
}
