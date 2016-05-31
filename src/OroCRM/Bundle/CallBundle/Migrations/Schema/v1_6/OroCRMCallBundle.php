<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL92Platform;
use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\SqlMigrationQuery;

class OroCRMCallBundle implements Migration, DatabasePlatformAwareInterface
{
    /** @var AbstractPlatform */
    protected $platform;

    /** {@inheritdoc} */
    public function setDatabasePlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

    /** {@inheritdoc} */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addPreQuery($this->getPlatformSQL());
    }

    /**
     * Gets platform dependant sql for migration
     *
     * @return SqlMigrationQuery
     */
    private function getPlatformSQL()
    {
        $migrateDataSQL = 'UPDATE orocrm_call SET duration =' .
                          ' EXTRACT(HOUR FROM duration_old) * 3600 +' .
                          ' EXTRACT(MINUTE FROM duration_old) * 60 +' .
                          ' EXTRACT(SECOND FROM duration_old) * 1';

        if ($this->platform instanceof PostgreSQL92Platform) {
            return new SqlMigrationQuery(
                [
                    'ALTER TABLE orocrm_call RENAME COLUMN duration TO duration_old',
                    'ALTER TABLE orocrm_call ADD COLUMN duration int NULL DEFAULT NULL',
                    'COMMENT ON COLUMN orocrm_call.duration IS \'(DC2Type:duration)\'',
                    $migrateDataSQL,
                    $this->getPlatformUpdateEntityConfigTypeSQL(),
                    'ALTER TABLE orocrm_call DROP COLUMN duration_old',
                ]
            );
        }

        return new SqlMigrationQuery(
            [
                'ALTER TABLE orocrm_call CHANGE duration duration_old TIME NULL DEFAULT NULL',
                'ALTER TABLE orocrm_call ADD COLUMN duration int NULL DEFAULT NULL' .
                ' COMMENT \'(DC2Type:duration)\'',
                $migrateDataSQL,
                $this->getPlatformUpdateEntityConfigTypeSQL(),
                'ALTER TABLE orocrm_call DROP COLUMN duration_old',
            ]
        );
    }

    private function getPlatformUpdateEntityConfigTypeSQL()
    {
        $callEntityClass = addslashes('OroCRM\\Bundle\\CallBundle\\Entity\\Call');

        if ($this->platform instanceof PostgreSQL92Platform) {
            return 'UPDATE oro_entity_config_field AS f' .
                   ' SET f.type=\'duration\'' .
                   ' FROM oro_entity_config AS c' .
                   ' WHERE f.entity_id = c.id' .
                   ' AND c.class_name = \'' . $callEntityClass . '\'' .
                   ' AND f.field_name = \'duration\'';
        }

        return 'UPDATE oro_entity_config_field AS f' .
               ' INNER JOIN oro_entity_config c ON f.entity_id = c.id' .
               ' SET f.type = \'duration\'' .
               ' WHERE c.class_name = \'' . $callEntityClass . '\'' .
               ' AND f.field_name = \'duration\'';
    }
}
