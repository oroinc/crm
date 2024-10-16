<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_24;

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

class AddLeadStatus implements
    Migration,
    OutdatedExtendExtensionAwareInterface,
    ExtendOptionsManagerAwareInterface,
    OrderedMigrationInterface
{
    use OutdatedExtendExtensionAwareTrait;
    use ExtendOptionsManagerAwareTrait;

    #[\Override]
    public function getOrder(): int
    {
        return 1;
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->extendOptionsManager->removeColumnOptions('orocrm_sales_lead', 'status');

        $this->addLeadStatusField($schema, $queries);
    }

    private function addLeadStatusField(Schema $schema, QueryBag $queries): void
    {
        $enumTable = $this->outdatedExtendExtension->addOutdatedEnumField(
            $schema,
            'orocrm_sales_lead',
            'status',
            'lead_status',
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
        $options->set('enum', 'immutable_codes', ['new', 'qualified', 'canceled']);
        $enumTable->addOption(OroOptions::KEY, $options);

        $queries->addPostQuery(new OutdatedInsertEnumValuesQuery($this->outdatedExtendExtension, 'lead_status', [
            new OutdatedEnumDataValue('new', 'New', 1, true),
            new OutdatedEnumDataValue('qualified', 'Qualified', 2),
            new OutdatedEnumDataValue('canceled', 'Disqualified', 3)
        ]));
    }
}
