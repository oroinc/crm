<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Doctrine repository for B2bCustomer entity.
 */
class B2bCustomerRepository extends EntityRepository
{
    public const VALUABLE_STATUS = 'won';

    public function calculateLifetimeValue(
        B2bCustomer $customer,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ): float {
        $associationName = AccountCustomerManager::getCustomerTargetField(B2bCustomer::class);

        $qb = $this->getEntityManager()->getRepository(Opportunity::class)
            ->createQueryBuilder('o');
        $qb->select(sprintf('SUM(%s)', $qbTransformer->getTransformSelectQuery('closeRevenue', $qb)));
        $qb->innerJoin('o.customerAssociation', 'c');
        $qb->where(sprintf('c.%s = :customer', $associationName));
        $qb->andWhere("JSON_EXTRACT(o.serialized_data, 'status') = :status");
        $qb->setParameter('customer', $customer);
        $qb->setParameter(
            'status',
            ExtendHelper::buildEnumOptionId(
                Opportunity::INTERNAL_STATUS_CODE,
                self::VALUABLE_STATUS
            )
        );

        return (float)$qb->getQuery()->getSingleScalarResult();
    }
}
