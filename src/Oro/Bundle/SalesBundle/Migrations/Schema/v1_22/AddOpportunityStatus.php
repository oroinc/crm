<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Migration\Query\EnumDataValue;
use Oro\Bundle\EntityExtendBundle\Migration\Query\InsertEnumValuesQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddOpportunityStatus implements
    Migration,
    ExtendExtensionAwareInterface,
    ExtendOptionsManagerAwareInterface,
    OrderedMigrationInterface
{
    use ExtendExtensionAwareTrait;
    use ExtendOptionsManagerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function getOrder(): int
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->extendOptionsManager->removeColumnOptions('orocrm_sales_opportunity', 'status');

        $this->addOpportunityStatusField($schema, $queries);
    }

    private function addOpportunityStatusField(Schema $schema, QueryBag $queries): void
    {
        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'orocrm_sales_opportunity',
            'status',
            'opportunity_status',
            false,
            false,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE],
                'dataaudit' => ['auditable' => true],
                'importexport' => ['order' => 90, 'short' => true]
            ]
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', ['in_progress', 'won', 'lost']);
        $enumTable->addOption(OroOptions::KEY, $options);

        $queries->addPostQuery(new InsertEnumValuesQuery($this->extendExtension, 'opportunity_status', [
            new EnumDataValue('in_progress', 'In Progress', 1, true),
            new EnumDataValue('identification_alignment', 'Identification & Alignment', 2),
            new EnumDataValue('needs_analysis', 'Needs Analysis', 3),
            new EnumDataValue('solution_development', 'Solution Development', 4),
            new EnumDataValue('negotiation', 'Negotiation', 5),
            new EnumDataValue('won', 'Closed Won', 6),
            new EnumDataValue('lost', 'Closed Lost', 7)
        ]));
    }
}
