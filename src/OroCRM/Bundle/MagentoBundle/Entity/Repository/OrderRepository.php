<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;


class OrderRepository extends EntityRepository
{
    /**
     * @param Cart|Customer $item
     * @param string        $field
     *
     * @return Cart|Customer|null $item
     * @throws InvalidEntityException
     */
    public function getLastPlacedOrderBy($item, $field)
    {
        if (!($item instanceof Cart) && !($item instanceof Customer)) {
            throw new InvalidEntityException();
        }
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.' . $field . ' = :item');
        $qb->setParameter('item', $item);
        $qb->orderBy('o.updatedAt', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getCustomerOrdersSubtotalAmount(Customer $customer, Order $order)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('sum(o.subtotalAmount - o.totalCanceledAmount - o.totalRefundedAmount) as subtotal')
            ->where('o.customer = :customer')
            ->setParameter('customer', $customer);

        if ($order->getId()) {
            $qb->andWhere('o.id != :orderId')
                ->setParameter('orderId', $order->getId());
        }

        return $qb->getQuery()
            ->getSingleScalarResult();
    }
}
