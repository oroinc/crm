<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

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
        $associationName = AccountCustomerManager::getCustomerTargetField(B2bCustomer::class);

        $qb = $this->getEntityManager()->getRepository('OroSalesBundle:Opportunity')
            ->createQueryBuilder('o');
        $closeRevenueQuery = $qbTransformer->getTransformSelectQuery('closeRevenue', $qb);
        $qb->select(sprintf('SUM(%s)', $closeRevenueQuery));
        $qb->innerJoin('o.customerAssociation', 'c');
        $qb->innerJoin('o.status', 's');
        $qb->andWhere(sprintf('c.%s = :customer', $associationName));
        $qb->andWhere('s.id = :status');
        $qb->setParameter('customer', $customer);
        $qb->setParameter('status', self::VALUABLE_STATUS);

        return (float)$qb->getQuery()->getSingleScalarResult();
    }
}
