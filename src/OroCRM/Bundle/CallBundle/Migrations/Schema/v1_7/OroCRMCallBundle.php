<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL92Platform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\MigrationBundle\Migration\Extension\DatabasePlatformAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
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
        // migrate column to new type
        $queries->addPreQuery($this->getPlatformSQL());

        // migrate oro_entity_config_field
        $entityClass = 'OroCRM\\Bundle\\CallBundle\\Entity\\Call';

        $migrateFieldSql = 'UPDATE oro_entity_config_field SET type = :field_type' .
                           ' WHERE field_name = :field_name' .
                           ' AND entity_id IN' .
                           ' (SELECT id FROM oro_entity_config WHERE class_name = :class_name)';
        $migrateEntityFieldQuery = new ParametrizedSqlMigrationQuery();
        $migrateEntityFieldQuery->addSql(
            $migrateFieldSql,
            ['field_type' => 'duration', 'field_name' => 'duration', 'class_name' => $entityClass],
            ['field_type' => Type::STRING, 'field_name' => Type::STRING, 'class_name' => Type::STRING]
        );

        $queries->addPostQuery($migrateEntityFieldQuery);
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
                'ALTER TABLE orocrm_call DROP COLUMN duration_old',
            ]
        );
    }
}
