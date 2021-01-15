<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;

/**
 * Update organization_id field with organization_id from account
 */
class UpdateB2bCustomerOrganizationData extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->updateB2bCustomerOrganization($manager);
    }

    /**
     * @param EntityManager $em
     */
    protected function updateB2bCustomerOrganization(ObjectManager $em): void
    {
        $connection = $em->getConnection();

        $connection->executeQuery(
            <<<SQL
UPDATE orocrm_sales_b2bcustomer as c
SET organization_id = (
    SELECT
        a.organization_id
    FROM
        orocrm_account as a
    WHERE
        a.id = c.account_id
);
SQL
        );
    }
}
