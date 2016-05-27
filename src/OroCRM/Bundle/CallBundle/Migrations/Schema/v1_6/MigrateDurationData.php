<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateDurationData implements Migration, OrderedMigrationInterface
{
    /** {@inheritdoc} */
    public function getOrder()
    {
        return 2;
    }

    /** {@inheritdoc} */
    public function up(Schema $schema, QueryBag $queries)
    {
        // migrate data
        $queries->addPreQuery(
            'UPDATE orocrm_call SET duration =' .
            ' EXTRACT(HOUR FROM duration_old) * 3600 +' .
            ' EXTRACT(MINUTE FROM duration_old) * 60 +' .
            ' EXTRACT(SECOND FROM duration_old) * 1'
        );

        $schema->getTable('orocrm_call')
               ->dropColumn('duration_old')
        ;
    }
}
