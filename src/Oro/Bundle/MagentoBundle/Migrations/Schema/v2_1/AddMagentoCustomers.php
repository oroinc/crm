<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Schema\v2_1;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;

use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class AddMagentoCustomers implements Migration, OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $associationName = ExtendHelper::buildAssociationName(
            MagentoCustomer::class,
            CustomerScope::ASSOCIATION_KIND
        );
        $sql = sprintf(
            'INSERT INTO orocrm_sales_customer (%s) SELECT id FROM orocrm_magento_customer',
            $associationName
        );
        $queries->addQuery($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
