<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerRepository extends EntityRepository
{
    const VALUABLE_STATUS = 'won';

    /**
     * Calculates the lifetime value for the given customer
     *
     * @param B2bCustomer $customer
     *
     * @return float
     */
    public function calculateLifetimeValue(B2bCustomer $customer)
    {
        $qb = $this->getEntityManager()->getRepository('OroCRMSalesBundle:Opportunity')
            ->createQueryBuilder('o');
        $qb->select('SUM(o.closeRevenue)');
        $qb->innerJoin('o.customer', 'c');
        $qb->innerJoin('o.status', 's');
        $qb->andWhere('c = :customer');
        $qb->andWhere('s.id = :status');
        $qb->setParameter('customer', $customer);
        $qb->setParameter('status', self::VALUABLE_STATUS);

        return (float)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Calculates new lifetime value for customer
     *
     * @param B2bCustomer $b2bCustomer
     *
     * @return bool Returns true if value was changed, false otherwise
     *
     * @deprecated Use {@see calculateLifetimeValue} instead
     */
    public function calculateLifetime(B2bCustomer $b2bCustomer)
    {
        $currentLifetime = $b2bCustomer->getLifetime();

        $b2bCustomer->setLifetime($this->calculateLifetimeValue($b2bCustomer));

        return $b2bCustomer->getLifetime() != $currentLifetime;
    }
}
