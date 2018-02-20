<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;

class AddCustomerAssociations implements
    Migration,
    CustomerExtensionAwareInterface,
    OrderedMigrationInterface
{
    use CustomerExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->customerExtension->addCustomerAssociation($schema, 'orocrm_sales_b2bcustomer');
    }
}
