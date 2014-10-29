<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMCallBundle implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery(
            new ParametrizedSqlMigrationQuery(
                'UPDATE orocrm_call SET created_at = :date, updated_at = :date',
                ['date' => new \DateTime('now', new \DateTimeZone('UTC'))],
                ['date' => Type::DATETIME]
            )
        );
        $table = $schema->getTable('orocrm_call');
        $table->getColumn('created_at')->setOptions(['notnull' => true]);
        $table->getColumn('updated_at')->setOptions(['notnull' => true]);
    }
}
