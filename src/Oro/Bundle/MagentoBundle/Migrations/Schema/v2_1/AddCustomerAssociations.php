<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;


class AddCustomerAssociations implements Migration, CustomerExtensionAwareInterface, OrderedMigrationInterface
{
    use CustomerExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->customerExtension->addCustomerAssociation($schema, 'orocrm_magento_customer');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
