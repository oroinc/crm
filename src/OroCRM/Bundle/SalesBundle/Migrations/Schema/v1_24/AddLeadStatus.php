<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_24;

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

use OroCRM\Bundle\SalesBundle\Entity\Lead;

class AddLeadStatus implements
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
        $extendOptionsManager->removeColumnOptions('orocrm_sales_lead', 'status');
        self::addStatusField($schema, $this->extendExtension, $queries);
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
            'orocrm_sales_lead',
            'status',
            Lead::INTERNAL_STATUS_CODE,
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
                'new',
                'qualified',
                'canceled'
            ]
        );

        $enumTable->addOption(OroOptions::KEY, $options);
        $statuses = [
            'new'       => 'New',
            'qualified' => 'Qualified',
            'canceled'  => 'Disqualified'
        ];
        
        $i = 1;
        foreach ($statuses as $key => $value) {
            $dropFieldsQuery = new ParametrizedSqlMigrationQuery();
            $dropFieldsQuery->addSql(
                'INSERT INTO oro_enum_lead_status (id, name, priority, is_default)
                          VALUES (:id, :name, :priority, :is_default)',
                ['id' => $key, 'name' => $value, 'priority' => $i, 'is_default' => 'new' === $key],
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
