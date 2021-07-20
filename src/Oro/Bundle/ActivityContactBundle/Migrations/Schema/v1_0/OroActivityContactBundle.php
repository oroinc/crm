<?php

namespace Oro\Bundle\ActivityContactBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroActivityContactBundle implements Migration
{
    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::removeUserACFields($schema, $queries);
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public static function removeUserACFields(Schema $schema, QueryBag $queries)
    {
        $tableName = 'oro_user';
        $className = 'Oro\Bundle\UserBundle\Entity\User';
        $queries->addPostQuery(new UpdateConfigQuery());
        $table = $schema->getTable($tableName);
        foreach (array_keys(ActivityScope::$fieldsConfiguration) as $fieldName) {
            if ($table->hasColumn($fieldName)) {
                $table->dropColumn($fieldName);
                $queries->addQuery(
                    OroActivityContactBundle::getDropEntityConfigFieldQuery(
                        $className,
                        $fieldName
                    )
                );
            }
        }
    }

    /**
     * @param string $className
     * @param string $fieldName
     *
     * @return ParametrizedSqlMigrationQuery
     */
    public static function getDropEntityConfigFieldQuery($className, $fieldName)
    {
        $dropFieldIndexSql = 'DELETE FROM oro_entity_config_index_value'
            . ' WHERE entity_id IS NULL AND field_id IN ('
            . ' SELECT oecf.id FROM oro_entity_config_field AS oecf'
            . ' WHERE oecf.field_name = :field'
            . ' AND oecf.entity_id IN ('
            . ' SELECT oec.id'
            . ' FROM oro_entity_config AS oec'
            . ' WHERE oec.class_name = :class'
            . ' ))';
        $dropFieldSql      = 'DELETE FROM oro_entity_config_field'
            . ' WHERE field_name = :field'
            . ' AND entity_id IN ('
            . ' SELECT id'
            . ' FROM oro_entity_config'
            . ' WHERE class_name = :class'
            . ' )';

        $query = new ParametrizedSqlMigrationQuery();
        $query->addSql(
            $dropFieldIndexSql,
            ['field' => $fieldName, 'class' => $className],
            ['field' => 'string', 'class' => 'string']
        );
        $query->addSql(
            $dropFieldSql,
            ['field' => $fieldName, 'class' => $className],
            ['field' => 'string', 'class' => 'string']
        );

        return $query;
    }
}
