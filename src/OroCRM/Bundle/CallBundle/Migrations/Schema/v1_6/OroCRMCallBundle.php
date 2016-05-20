<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\LocaleBundle\DoctrineExtensions\DBAL\Types\DurationType;
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
        $table->changeColumn('duration', ['type' => DurationType::getType('duration')]);
    }
}
