<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateTaskEntityIcon implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'OroCRM\Bundle\TaskBundle\Entity\Task',
                'entity',
                'icon',
                'icon-tasks'
            )
        );
    }
}
