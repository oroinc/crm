<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_22;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigModel;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class AddOpportunityState implements Migration, ExtendExtensionAwareInterface
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
        self::addStateField($schema, $this->extendExtension);
    }

    /**
     * @param Schema $schema
     * @param ExtendExtension $extendExtension
     */
    public static function addStateField(Schema $schema, ExtendExtension $extendExtension)
    {
        $enumTable = $extendExtension->addEnumField(
            $schema,
            'orocrm_sales_opportunity',
            'state',
            Opportunity::INTERNAL_STATE_CODE,
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
}
