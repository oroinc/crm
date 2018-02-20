<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v1_31;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_31\Query\MigrateB2bCustomersQuery;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_31\Query\UpdateLeadsQuery;
use Oro\Bundle\SalesBundle\Migrations\Schema\v1_31\Query\UpdateOpportunitiesQuery;

class UpdateBusinessCustomers implements
    Migration,
    OrderedMigrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $customerColumnName = AccountCustomerManager::getCustomerTargetField(B2bCustomer::class) . '_id';

        $queries->addQuery(new MigrateB2bCustomersQuery($customerColumnName, $schema));
        $queries->addQuery(new UpdateLeadsQuery($customerColumnName));
        $queries->addQuery(new UpdateOpportunitiesQuery($customerColumnName));
    }
}
