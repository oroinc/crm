<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerRepository extends EntityRepository
{
    const VALUABLE_STATUS = 'won';

    /**
     * Calculates the lifetime value for the given customer
     *
     * @param B2bCustomer $customer
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     *
     * @return float
     */
    public function calculateLifetimeValue(
        B2bCustomer $customer,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $qb = $this->getEntityManager()->getRepository('OroSalesBundle:Opportunity')
            ->createQueryBuilder('o');
        $closeRevenueQuery = $qbTransformer->getTransformSelectQuery('closeRevenue', $qb);
        $qb->select(sprintf('SUM(%s)', $closeRevenueQuery));
        $qb->innerJoin('o.customer', 'c');
        $qb->innerJoin('o.status', 's');
        $qb->andWhere('c = :customer');
        $qb->andWhere('s.id = :status');
        $qb->setParameter('customer', $customer);
        $qb->setParameter('status', self::VALUABLE_STATUS);

        return (float)$qb->getQuery()->getSingleScalarResult();
    }
}
