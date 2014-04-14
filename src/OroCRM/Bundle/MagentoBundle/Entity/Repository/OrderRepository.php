<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;

class OrderRepository extends EntityRepository
{
    /**
     * @param Entity $item
     * @param string $field
     *
     * @return Entity|null $item
     */
    public function getLastPlacedOrderBy($item, $field)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.' . $field . ' = :item');
        $qb->setParameter('item', $item);
        $qb->orderBy('o.updatedAt', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
