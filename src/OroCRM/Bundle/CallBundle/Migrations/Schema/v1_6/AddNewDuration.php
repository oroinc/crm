<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL92Platform;
use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddNewDuration
    implements Migration, DatabasePlatformAwareInterface, OrderedMigrationInterface
{
    /** @var AbstractPlatform */
    protected $platform;

    /** {@inheritdoc} */
    public function getOrder()
    {
        return 1;
    }

    /** {@inheritdoc} */
    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

    /** {@inheritdoc} */
    public function up(Schema $schema, QueryBag $queries)
    {
        // backup old column for migration
        if ($this->platform instanceof PostgreSQL92Platform) {
            $queries->addPreQuery(
                'ALTER TABLE orocrm_call RENAME COLUMN duration TO duration_old'
            );
        } else {
            $queries->addPreQuery(
                'ALTER TABLE orocrm_call CHANGE COLUMN `duration` `duration_old` TIME NULL'
            );
        }

        $schema->getTable('orocrm_call')
               ->addColumn('duration', 'duration', ['notnull' => false, 'default' => null])
        ;
    }
}
