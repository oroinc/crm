<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Schema\ExtendColumn;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCrmMagentoBundle implements Migration, ExtendExtensionAwareInterface
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
        /**
         * Enable notes for Cart and Order entities
         */
        $noteTable  = $schema->getTable('oro_note');
        $cartTable  = $schema->getTable('orocrm_magento_cart');
        $orderTable = $schema->getTable('orocrm_magento_order');

        $options['note']['enabled'] = true;

        $cartTable->addOption(ExtendColumn::ORO_OPTIONS_NAME, $options);
        $orderTable->addOption(ExtendColumn::ORO_OPTIONS_NAME, $options);

        $cartAssociationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName('orocrm_magento_cart')
        );
        $orderAssociationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName('orocrm_magento_order')
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $noteTable,
            $cartAssociationName,
            $cartTable,
            'email',
            ['extend' => ['owner' => 'Custom', 'is_extend' => true]]
        );
        $this->extendExtension->addManyToOneRelation(
            $schema,
            $noteTable,
            $orderAssociationName,
            $orderTable,
            'customer_email',
            ['extend' => ['owner' => 'Custom', 'is_extend' => true]]
        );
    }
}
