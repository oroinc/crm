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
        $defaultStatuses = [
            'in_progress',
            'won',
            'lost'
        ];
        $connection = $this->container->get('doctrine')->getConnection();

        /** @var ExtendOptionsManager $extendOptionsManager */
        $extendOptionsManager = $this->container->get('oro_entity_extend.migration.options_manager');
        $extendOptionsManager->removeColumnOptions('orocrm_sales_opportunity', 'status');

        self::addStatusField($schema, $this->extendExtension, $queries);

        $oldStatuses = $connection->fetchAll('SELECT name, label FROM orocrm_sales_opport_status');
        $newStatuses = $connection->fetchAll('SELECT id, priority FROM oro_enum_opportunity_status');
        $oldStatuses = $this->buildOneDimensionArray($oldStatuses, 'name', 'label');
        $newStatuses = $this->buildOneDimensionArray($newStatuses, 'id', 'priority');
        $oldStatusesToAdd = array_diff_key($oldStatuses, $newStatuses);

        if (is_array($oldStatusesToAdd) && count($oldStatusesToAdd)) {
            $defaultStatuses = array_merge($defaultStatuses, array_keys($oldStatusesToAdd));
        }

        $priority = 1;
        if (is_array($newStatuses) && count($newStatuses)) {
            $priority = max($newStatuses) + 1;
        }

        $this->updateOpportunityStatusTable($queries, $oldStatusesToAdd, $priority);
        $this->updateOpportunityTable($queries, $defaultStatuses);
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
                'in_progress',
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
            'in_progress' => 'In Progress',
            'won' => 'Closed Won',
            'lost' => 'Closed Lost'
        ];
        $defaultValue = 'in_progress';
        $query = 'INSERT INTO oro_enum_opportunity_status (id, name, priority, is_default)
                  VALUES (:id, :name, :priority, :is_default)';
        $i = 1;
        foreach ($statuses as $key => $value) {
            $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
            $dropFieldsQuery->addSql(
                $query,
                ['id' => $key, 'name' => $value, 'priority' => $i, 'is_default' => $defaultValue === $key],
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

    /**
     * @param QueryBag $queries
     * @param array $statuses
     * @param int $priority
     */
    protected function updateOpportunityStatusTable($queries, $statuses, $priority)
    {
        foreach ($statuses as $id => $name) {
            $query  = 'INSERT INTO oro_enum_opportunity_status (id, name, priority, is_default)
                       VALUES (:id, :name, :priority, 0)';
            $params = ['id' => $id, 'name' => $name, 'priority' => $priority];
            $types  = ['id' => Type::STRING, 'name' => Type::STRING, 'priority' => Type::INTEGER];

            $migrationQuery = new ParametrizedSqlMigrationQuery();
            $migrationQuery->addSql(
                $query,
                $params,
                $types
            );
            $queries->addPostQuery($migrationQuery);

            $priority++;
        }
    }

    /**
     * @param QueryBag $queries
     * @param array $statuses
     */
    protected function updateOpportunityTable($queries, $statuses)
    {
        $query = 'UPDATE orocrm_sales_opportunity SET status_id = :status_id WHERE status_name = :status_name';
        foreach ($statuses as $status) {
            $migrationQuery = new ParametrizedSqlMigrationQuery();
            $migrationQuery->addSql(
                $query,
                ['status_id' => $status, 'status_name' => $status],
                ['status_id' => Type::STRING, 'status_name' => Type::STRING]
            );
            $queries->addPostQuery($migrationQuery);
        }
    }

    /**
     * @param array $rows
     * @param string $key
     * @param string $value
     *
     * @return array
     */
    protected function buildOneDimensionArray($rows, $key, $value)
    {
        $result = [];

        foreach ($rows as $row) {
            $result[$row[$key]] = $row[$value];
        }

        return $result;
    }
}
