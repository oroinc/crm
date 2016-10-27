<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMagentoBundle implements Migration, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $opportunityTable = $schema->getTable('orocrm_sales_opportunity');

        $options = new OroOptions();
        $options->append(
            'sales',
            'customers',
            [
                'Oro\Bundle\MagentoBundle\Entity\Customer' => 'magentoCustomer',
            ]
        );
        $opportunityTable->addOption(OroOptions::KEY, $options);

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $opportunityTable,
            'magentoCustomer',
            'orocrm_magento_customer',
            'id',
            [
                 'extend' => ['owner' => ExtendScope::OWNER_CUSTOM],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
