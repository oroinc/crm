<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;

class MoveOldStatusesToNewEnumTable implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 4;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::processMigration($queries);
    }

    /**
     * @param QueryBag $queries
     */
    public static function processMigration(QueryBag $queries)
    {
        $queries->addQuery(new MoveOldStatusesToNewEnumTableQuery());
    }
}
