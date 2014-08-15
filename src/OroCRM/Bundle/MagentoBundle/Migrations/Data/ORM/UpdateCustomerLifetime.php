<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;



use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

class UpdateCustomerLifetime extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        /** @var EntityRepository $repository */
        $repository = $manager->getRepository('OroCRMMagentoBundle:Customer');
        $queryBuilder = $repository->createQueryBuilder('customer')
            ->select('customer.id, SUM(customerOrder.subtotalAmount) as lifetime')
            ->leftJoin('customer.orders', 'customerOrder', 'WITH', 'customerOrder.status != :status')
            ->groupBy('customer.id')
            ->orderBy('customer.id');
        $queryBuilder->setParameter('status', 'canceled');

        $updateQuery =
            'UPDATE OroCRMMagentoBundle:Customer customer SET customer.lifetime = :lifetime WHERE customer.id = :id';

        // update lifetime for all customers
        $iterator = new BufferedQueryResultIterator($queryBuilder);
        foreach ($iterator as $row) {
            $customerId = $row['id'];
            $lifetime = $row['lifetime'] ?: 0;
            $manager->createQuery($updateQuery)
                ->setParameter('id', $customerId)
                ->setParameter('lifetime', $lifetime)
                ->execute();
        }
    }
}
