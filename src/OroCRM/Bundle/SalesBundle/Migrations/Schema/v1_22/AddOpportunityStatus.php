<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManager;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class AddOpportunityStatus implements
    Migration,
    ExtendExtensionAwareInterface,
    ContainerAwareInterface,
    OrderedMigrationInterface
{
    /** @var ContainerInterface */
    protected $container;

    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

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

        self::addStatusField($schema, $this->extendExtension, $queries);

        $statusMapping = [
            'won' => 'won',
            'lost' => 'lost',
            'in_progress' => 'solution_development'
        ];
        $query = 'UPDATE orocrm_sales_opportunity SET status_id = :status_id WHERE status_name = :status_name';
        foreach ($statusMapping as $oldStatus => $newStatus) {
            $migrationQuery = new ParametrizedSqlMigrationQuery();
            $migrationQuery->addSql(
                $query,
                ['status_id' => $newStatus, 'status_name' => $oldStatus],
                ['status_id' => Type::STRING, 'status_name' => Type::STRING]
            );
            $queries->addPostQuery($migrationQuery);
        }
    }

    /**
     * @param Schema $schema
     * @param ExtendExtension $extendExtension
     * @param QueryBag $queries
     */
    public static function addStatusField(Schema $schema, ExtendExtension $extendExtension, QueryBag $queries)
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
        $statuses = [
            'identification_alignment' => 'Identification & Alignment',
            'needs_analysis' => 'Needs Analysis',
            'solution_development' => 'Solution Development',
            'negotiation' => 'Negotiation',
            'won' => 'Closed Won',
            'lost' => 'Closed Lost'
        ];
        $query = 'INSERT INTO oro_enum_opportunity_status (id, name, priority, is_default)
                  VALUES (:id, :name, :priority, :is_default)';
        $i = 1;
        foreach ($statuses as $key => $value) {
            $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
            $dropFieldsQuery->addSql(
                $query,
                ['id' => $key, 'name' => $value, 'priority' => $i, 'is_default' => 0],
                [
                    'id' => Type::STRING,
                    'name' => Type::STRING,
                    'priority' => Type::INTEGER,
                    'is_default' => Type::BOOLEAN
                ]
            );
            $queries->addQuery($dropFieldsQuery);
            $i++;
        }
    }
}
