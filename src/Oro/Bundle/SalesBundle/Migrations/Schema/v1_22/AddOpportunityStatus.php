<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddOpportunityStatus implements
    Migration,
    ExtendExtensionAwareInterface,
    ContainerAwareInterface,
    OrderedMigrationInterface
{
    /** @var ContainerInterface */
    protected $container;

    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** @var ExtendOptionsManager $extendOptionsManager */
        $extendOptionsManager = $this->container->get('oro_entity_extend.migration.options_manager');
        $extendOptionsManager->removeColumnOptions('orocrm_sales_opportunity', 'status');

        $immutableCodes = ['in_progress', 'won', 'lost'];

        self::addStatusField($schema, $this->extendExtension, $immutableCodes);

        $statuses = [
            'in_progress' => 'In Progress',
            'identification_alignment' => 'Identification & Alignment',
            'needs_analysis' => 'Needs Analysis',
            'solution_development' => 'Solution Development',
            'negotiation' => 'Negotiation',
            'won' => 'Closed Won',
            'lost' => 'Closed Lost',
        ];

        self::addEnumValues($queries, $statuses);
    }

    public static function addStatusField(Schema $schema, ExtendExtension $extendExtension, array $immutableCodes)
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
            $immutableCodes
        );

        $enumTable->addOption(OroOptions::KEY, $options);
    }

    public static function addEnumValues(QueryBag $queries, array $statuses, $defaultValue = 'in_progress')
    {
        $query = 'INSERT INTO oro_enum_opportunity_status (id, name, priority, is_default)
                  VALUES (:id, :name, :priority, :is_default)';
        $i = 1;
        foreach ($statuses as $key => $value) {
            $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
            $dropFieldsQuery->addSql(
                $query,
                ['id' => $key, 'name' => $value, 'priority' => $i, 'is_default' => $defaultValue === $key],
                [
                    'id' => Types::STRING,
                    'name' => Types::STRING,
                    'priority' => Types::INTEGER,
                    'is_default' => Types::BOOLEAN
                ]
            );
            $queries->addQuery($dropFieldsQuery);
            $i++;
        }
    }
}
