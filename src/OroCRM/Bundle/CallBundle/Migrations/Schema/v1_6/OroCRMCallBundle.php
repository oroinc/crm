<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

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
        $queries->addPreQuery(new UpdateCallDurationToIntegerQuery());
        $table = $schema->getTable('orocrm_call');
        $table->changeColumn('duration', ['type' => 'duration', 'default' => null]);
    }
}
