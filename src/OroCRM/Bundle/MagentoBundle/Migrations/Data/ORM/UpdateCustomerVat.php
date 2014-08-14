<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

class UpdateCustomerVat extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        $query = 'UPDATE OroCRMMagentoBundle:Customer customer ' .
            'SET customer.vat = customer.vat/100.0 ' .
            'WHERE customer.vat IS NOT NULL';
        $manager->createQuery($query)->execute();
    }
}
