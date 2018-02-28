<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;
use Oro\Bundle\MagentoBundle\Entity\Order;

/**
 * Recalculate lifetime value for customers that have canceled orders.
 */
class UpdateCustomerLifetimeForCanceledOrders extends AbstractDefaultChannelDataFixture
{
    const BUFFER_SIZE = 10000;

    /** @var EntityRepository */
    protected $customerRepository;

    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $manager */
        $this->customerRepository = $manager->getRepository('OroMagentoBundle:Customer');

        // Calculate lifetime value for all customers
        $queryBuilder = $this->customerRepository->createQueryBuilder('customer');
        $queryBuilder
            ->select(
                'SUM(
                    CASE WHEN customerOrder.subtotalAmount IS NOT NULL THEN customerOrder.subtotalAmount ELSE 0 END -
                    CASE WHEN customerOrder.discountAmount IS NOT NULL THEN ABS(customerOrder.discountAmount) ELSE 0 END
                ) AS lifetime',
                'customer.id as customerId',
                'customer.lifetime AS oldLifetime',
                'IDENTITY(customer.dataChannel) as dataChannelId'
            )
            ->leftJoin(
                'customer.orders',
                'customerOrder',
                'WITH',
                $queryBuilder->expr()->neq($queryBuilder->expr()->lower('customerOrder.status'), ':status')
            )
            ->groupBy('customer.account, customer.id')
            ->orderBy('customer.account')
            ->setParameter('status', Order::STATUS_CANCELED);

        // Get lifetime value only for customers that have canceled orders
        $this->addFilterByOrderStatus($queryBuilder, Order::STATUS_CANCELED);

        $iterator = new BufferedIdentityQueryResultIterator($queryBuilder);
        $iterator->setBufferSize(self::BUFFER_SIZE);

        $channels = [];
        foreach ($iterator as $row) {
            if ($row['lifetime'] == $row['oldLifetime']) {
                continue;
            }
            $this->updateCustomerLifetimeValue($row['customerId'], $row['lifetime']);
            if (!isset($channels[$row['dataChannelId']])) {
                $channels[$row['dataChannelId']] = $row['dataChannelId'];
            }
        }
        foreach ($channels as $channelId) {
            /** @var Channel $channel */
            $channel = $manager->getReference('OroChannelBundle:Channel', $channelId);
            $this->updateLifetimeForAccounts($channel);
        }
    }

    /**
     * @param int    $customerId
     * @param string $value
     */
    protected function updateCustomerLifetimeValue($customerId, $value)
    {
        $qb = $this->customerRepository
            ->createQueryBuilder('c')
            ->update('OroMagentoBundle:Customer', 'c')
            ->set('c.lifetime', $value)
            ->where('c.id = :id')
            ->setParameter('id', $customerId);

        $qb->getQuery()->execute();
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $orderStatus
     */
    protected function addFilterByOrderStatus(QueryBuilder $qb, $orderStatus)
    {
        $aliases = $qb->getRootAliases();
        $subQueryBuilder = $this->customerRepository->createQueryBuilder('c');
        $subQueryBuilder
            ->select('IDENTITY(c.account)')
            ->join(
                'c.orders',
                'o',
                'WITH',
                $subQueryBuilder->expr()->eq($subQueryBuilder->expr()->lower('o.status'), ':filteredOrderStatus')
            );

        $qb->andWhere(
            $qb->expr()->in($aliases[0] . '.account', $subQueryBuilder->getDQL())
        )
            ->setParameter('filteredOrderStatus', $orderStatus);
    }
}
