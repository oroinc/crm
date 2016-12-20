<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_3;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Migrations\Schema\v2_3\Query\UpdateAccountsQuery;
use Oro\Bundle\SalesBundle\Migrations\Schema\v2_3\Query\UpdateLeadsQuery;
use Oro\Bundle\SalesBundle\Migrations\Schema\v2_3\Query\UpdateOpportunitiesQuery;

class UpdateBusinessCustomers implements 
    Migration,
    OrderedMigrationInterface,
    ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
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
    public function getOrder()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $customerColumnName = $this->getCustomerColumnName($schema);

        $queries->addQuery(new UpdateAccountsQuery($customerColumnName));
        $queries->addQuery(new UpdateLeadsQuery($customerColumnName));
        $queries->addQuery(new UpdateOpportunitiesQuery($customerColumnName));
    }

    /**
     * @param  Schema $schema
     * @return string
     */
    protected function getCustomerColumnName(Schema $schema)
    {
        $customersTable = $schema->getTable('orocrm_sales_b2bcustomer');
        $pkName = $this->extendExtension->getPrimaryKeyColumnName($customersTable);
        // $pkName = array_pop($customersTable->getPrimaryKey()->getColumns());
        $associationName = ExtendHelper::buildAssociationName(
            B2bCustomer::class,
            CustomerScope::ASSOCIATION_KIND
        );

        return $associationName . '_' . $pkName;
    }
}
