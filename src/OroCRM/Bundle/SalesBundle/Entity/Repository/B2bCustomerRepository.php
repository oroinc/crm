<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerRepository extends EntityRepository
{
    const VALUABLE_STATUS = 'won';

    /**
     * Calculates new lifetime value for customer
     *
     * @param B2bCustomer $b2bCustomer
     *
     * @return bool Returns true if value was changed, false otherwise
     */
    public function calculateLifetime(B2bCustomer $b2bCustomer)
    {
        $currentLifetime = $b2bCustomer->getLifetime();

        $qb = $this->getEntityManager()->getRepository('OroCRMSalesBundle:Opportunity')
            ->createQueryBuilder('o');
        $qb->select('SUM(o.closeRevenue)');
        $qb->innerJoin('o.customer', 'c');
        $qb->innerJoin('o.status', 's');
        $qb->andWhere('c = :customer');
        $qb->andWhere('s.name = :status');
        $qb->setParameter('customer', $b2bCustomer);
        $qb->setParameter('status', self::VALUABLE_STATUS);

        $b2bCustomer->setLifetime($qb->getQuery()->getSingleScalarResult());

        return $b2bCustomer->getLifetime() != $currentLifetime;
    }
}
