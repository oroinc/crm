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

    /** @var ExtendExtension */
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
    }

    /**
     * @param Schema $schema
     * @param ExtendExtension $extendExtension
     * @param QueryBag $queries
     * @param array $statusList
     * @param null $defaultStatus
     */
    public static function addStatusField(
        Schema $schema,
        ExtendExtension $extendExtension,
        QueryBag $queries,
        array $statusList = null,
        $defaultStatus = null
    )
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

        if (!empty($statusList)) {
            $statuses = $statusList;
        }

        $defaultValue = 'in_progress';

        if ($defaultStatus) {
            $defaultValue = $defaultStatus;
        }

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
}
