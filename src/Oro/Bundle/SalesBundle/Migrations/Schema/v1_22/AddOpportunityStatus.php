<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\ExtendOptionsManagerAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\OutdatedExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\OutdatedExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\EntityExtendBundle\Migration\Query\OutdatedEnumDataValue;
use Oro\Bundle\EntityExtendBundle\Migration\Query\OutdatedInsertEnumValuesQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddOpportunityStatus implements
    Migration,
    OutdatedExtendExtensionAwareInterface,
    ExtendOptionsManagerAwareInterface,
    OrderedMigrationInterface
{
    use OutdatedExtendExtensionAwareTrait;
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
        $enumTable = $this->outdatedExtendExtension->addOutdatedEnumField(
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

        $queries->addPostQuery(new OutdatedInsertEnumValuesQuery($this->outdatedExtendExtension, 'opportunity_status', [
            new OutdatedEnumDataValue('in_progress', 'In Progress', 1, true),
            new OutdatedEnumDataValue('identification_alignment', 'Identification & Alignment', 2),
            new OutdatedEnumDataValue('needs_analysis', 'Needs Analysis', 3),
            new OutdatedEnumDataValue('solution_development', 'Solution Development', 4),
            new OutdatedEnumDataValue('negotiation', 'Negotiation', 5),
            new OutdatedEnumDataValue('won', 'Closed Won', 6),
            new OutdatedEnumDataValue('lost', 'Closed Lost', 7)
        ]));
    }
}
