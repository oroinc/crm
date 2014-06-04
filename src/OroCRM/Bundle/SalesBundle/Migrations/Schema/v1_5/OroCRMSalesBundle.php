<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Schema\ExtendColumn;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCRMSalesBundle implements Migration, ExtendExtensionAwareInterface
{
    /** @var  ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
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
        self::addNoteAssociations($schema, $this->extendExtension);
    }

    /**
     * Enable notes for Lead and Opportunity entities
     *
     * @param Schema          $schema
     * @param ExtendExtension $extendExtension
     */
    public static function addNoteAssociations(Schema $schema, ExtendExtension $extendExtension)
    {
        $noteTable        = $schema->getTable('oro_note');
        $leadTable        = $schema->getTable('orocrm_sales_lead');
        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');

        $options['note']['enabled'] = true;

        $leadTable->addOption(ExtendColumn::ORO_OPTIONS_NAME, $options);
        $opportunityTable->addOption(ExtendColumn::ORO_OPTIONS_NAME, $options);

        $leadAssociationName = ExtendHelper::buildAssociationName(
            $extendExtension->getEntityClassByTableName('orocrm_sales_lead')
        );
        $opportunityAssociationName = ExtendHelper::buildAssociationName(
            $extendExtension->getEntityClassByTableName('orocrm_sales_opportunity')
        );

        $extendExtension->addManyToOneRelation(
            $schema,
            $noteTable,
            $leadAssociationName,
            $leadTable,
            'name',
            ['extend' => ['owner' => 'Custom', 'is_extend' => true]]
        );
        $extendExtension->addManyToOneRelation(
            $schema,
            $noteTable,
            $opportunityAssociationName,
            $opportunityTable,
            'name',
            ['extend' => ['owner' => 'Custom', 'is_extend' => true]]
        );
    }
}
