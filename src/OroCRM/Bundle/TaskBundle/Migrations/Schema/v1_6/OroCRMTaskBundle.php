<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMTaskBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_task');
        $table->getColumn('description')
            ->setType(Type::getType(Type::TEXT))
            ->setLength(null);
    }
}
