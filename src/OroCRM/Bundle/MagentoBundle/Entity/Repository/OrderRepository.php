<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;

class OrderRepository extends EntityRepository
{
    /**
     * Selects last placed order by given cart entity
     *
     * @param Cart $cart
     *
     * @return array
     */
    public function getLastPlacedOrderByCart(Cart $cart)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.cart = :cart');
        $qb->setParameter('cart', $cart);
        $qb->orderBy('o.updatedAt', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getSingleResult();
    }

    public function getLastPlacedOrderBy($item, $param)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.' . $param . ' = :item');
        $qb->setParameter('item', $item);
        $qb->orderBy('o.updatedAt', 'DESC');
        $qb->setMaxResults(1);

        var_dump( $qb->getQuery() );

        return $qb->getQuery()->getSingleResult();
    }
}
