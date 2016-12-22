<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
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
        $associationName = ExtendHelper::buildAssociationName(
            B2bCustomer::class,
            CustomerScope::ASSOCIATION_KIND
        );

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
