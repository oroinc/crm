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
                'UPDATE orocrm_call SET createdAt = :date',
                ['date' => new \DateTime('now', new \DateTimeZone('UTC'))],
                ['date' => Type::DATETIME]
            )
        );
        $table = $schema->createTable('oro_calendar_event');
        $table->getColumn('createdAt')->setOptions(['notnull' => true]);
    }
}
