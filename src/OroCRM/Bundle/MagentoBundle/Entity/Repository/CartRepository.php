<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class CartRepository extends EntityRepository
{
    /**
     * Get mageto carts by last month grouping by state
     *
     * @return array
     *  key - label of state
     *  value - sum of grand totals
     */
    public function getMagentoCartsByStates()
    {
        $dateEnd = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateStart = clone $dateEnd;
        $dateStart = $dateStart->sub(new \DateInterval('P1M'));
        $qb = $this->createQueryBuilder('cart');
        $qb->select('cart_status.label', 'SUM(cart.grandTotal) as total')
            ->join('cart.status', 'cart_status')
            ->where($qb->expr()->between('cart.createdAt', ':dateFrom', ':dateTo'))
            ->setParameter('dateFrom', $dateStart)
            ->setParameter('dateTo', $dateEnd)
            ->groupBy('cart_status.name');

        $data = $qb->getQuery()
            ->getArrayResult();

        $resultData = [];

        foreach ($data as $dataValue) {
            $resultData[$dataValue['label']] = (double)$dataValue['total'];
        }

        return $resultData;
    }
}
