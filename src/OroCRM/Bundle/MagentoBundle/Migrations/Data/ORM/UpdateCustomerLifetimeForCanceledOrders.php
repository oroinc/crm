<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;

/**
 * Recalculate lifetime value for customers who has canceled orders.
 */
class UpdateCustomerLifetimeForCanceledOrders extends AbstractFixture
{
    const BUFFER_SIZE = 50;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        /** @var OrderRepository $orderRepository */
        $orderRepository = $manager->getRepository('OroCRMMagentoBundle:Order');
        $queryBuilder = $manager->createQueryBuilder('c');
        $queryBuilder->select('c')
            ->from('OroCRMMagentoBundle:Customer', 'c')
            ->join('OroCRMMagentoBundle:Order', 'o', 'WITH', 'o.customer = c')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('o.status', ':status')
                )
            )
            ->setParameter('status', Order::STATUS_CANCELED)
            ->groupBy('c');

        $iterator = new BufferedQueryResultIterator($queryBuilder);
        $iterator->setBufferSize(self::BUFFER_SIZE);

        $requireFlush = false;
        $processed = 0;
        foreach ($iterator as $customer) {
            $processed++;
            $oldLifetime = (float)$customer->getLifetime();
            $newLifetime = $orderRepository->getCustomerOrdersSubtotalAmount($customer);
            if ($newLifetime !== $oldLifetime) {
                $customer->setLifetime($newLifetime);
                $manager->persist($customer);
                $requireFlush = true;
            }

            if ($processed === self::BUFFER_SIZE) {
                $processed = 0;
                $requireFlush = false;
                if ($requireFlush) {
                    $manager->flush();
                    $manager->clear();
                }
            }
        }

        if ($requireFlush) {
            $manager->flush();
            $manager->clear();
        }
    }
}
