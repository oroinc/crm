<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class AddOpportunityStatus implements Migration, ExtendExtensionAwareInterface
{
    protected $extendExtension;

    /**
     * @param ExtendExtension $extendExtension
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::deleteOldStatusFieldConfig($queries);
        self::addStatusField($schema, $this->extendExtension);
        self::hideRenamedStatusField($queries);
    }

    /**
     * @param Schema $schema
     * @param ExtendExtension $extendExtension
     */
    public static function addStatusField(Schema $schema, ExtendExtension $extendExtension)
    {
        $enumTable = $extendExtension->addEnumField(
            $schema,
            'orocrm_sales_opportunity',
            'status',
            Opportunity::INTERNAL_STATUS_CODE,
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'dataaudit' => ['auditable' => true],
                'importexport' => ["order" => 90, "short" => true]
            ]
        );

        $options = new OroOptions();
        $options->set(
            'enum',
            'immutable_codes',
            [
                'identification_alignment',
                'needs_analysis',
                'solution_development',
                'negotiation',
                'won',
                'lost'
            ]
        );

        $enumTable->addOption(OroOptions::KEY, $options);
    }
    
    /**
     * @param QueryBag $queries
     */
    public static function deleteOldStatusFieldConfig(QueryBag $queries)
    {
        $fieldName = 'status';
        $entityClass = 'OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity';
        $dropFieldsSql = <<<EOF
DELETE FROM oro_entity_config_field
WHERE field_name = :field_name
AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class_name)
EOF;

        $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
        $dropFieldsQuery->addSql(
            $dropFieldsSql,
            ['field_name' => $fieldName, 'class_name' => $entityClass],
            ['field_name' => Type::STRING, 'class_name' => Type::STRING]
        );
        $queries->addPreQuery($dropFieldsQuery);
    }
    
    /**
     * @param QueryBag $queries
     */
    public static function hideRenamedStatusField(QueryBag $queries)
    {
        $updateFieldsSql = <<<EOF
UPDATE oro_entity_config_field SET mode = :mode
WHERE field_name = :field_name
AND entity_id IN (SELECT id FROM oro_entity_config WHERE class_name = :class_name)
EOF;
        $entityClass = 'OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity';
        $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
        $dropFieldsQuery->addSql(
            $updateFieldsSql,
            ['mode' => ConfigModel::MODE_HIDDEN, 'field_name' => 'statusOld', 'class_name' => $entityClass],
            ['mode' => Type::STRING, 'field_name' => Type::STRING, 'class_name' => Type::STRING]
        );
        $queries->addPostQuery($dropFieldsQuery);
    }
}
