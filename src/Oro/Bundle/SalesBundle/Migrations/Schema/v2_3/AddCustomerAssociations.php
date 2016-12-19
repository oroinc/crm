<?php

namespace Oro\Bundle\SalesBundle\Migrations\Schema\v2_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\OrderedMigrationInterface;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionAwareInterface;
use Oro\Bundle\SalesBundle\Migration\Extension\CustomerExtensionTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AddCustomerAssociations implements
    Migration,
    CustomerExtensionAwareInterface,
    OrderedMigrationInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;
    use CustomerExtensionTrait;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * For existing installations with Channel that contains Business Customer:
     * 1. Add customer association
     * 2. Migrate business customers to customer associations
     * 2. Migrate data from orocrm_sales_b2bcustomer.account_id relation to orocrm_sales_customer(account_id, new_b2b_customer_rel)
     * 3. Remove customer_id from orocrm_sales_opportunity and orocrm_sales_lead
     * 4. Remove activity inheritance targets that refer b2bcustomer direct relations
     * 5. Add activity inheritance for new customer
     *
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($this->hasBusinessCustomers()) {
            $this->customerExtension->addCustomerAssociation($schema, 'orocrm_sales_b2bcustomer');
        }
    }

    /**
     * @return bool
     */
    protected function hasBusinessCustomers()
    {
        $query = <<<SQL
SELECT COUNT(*)
FROM orocrm_channel c
JOIN orocrm_channel_entity_name e ON e.channel_id = c.id
WHERE c.status = :status AND e.name = :customerClass;
SQL;
        $params = [
            'customerClass' => B2bCustomer::class,
            'status'  => true,
        ];
        $types = [
            'customerClass' => Type::STRING,
            'status'  => Type::BOOLEAN,
        ];

        return (bool) $this->getConnection()->executeQuery($query, $params, $types)->fetchColumn(0);
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->container->get('doctrine')->getConnection();
    }
}
