<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

class CustomerRepository extends EntityRepository
{
    /**
     * @param int    $targetId
     * @param string $targetField
     *
     * @return Customer|null
     */
    public function getCustomerByTarget($targetId, $targetField)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where(QueryBuilderUtil::sprintf('IDENTITY(c.%s) = :targetId', $targetField));

        return $qb->setParameter('targetId', $targetId)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * Returns Customer which have Account as target
     *
     * @param Account $account
     * @param array   $customersFields
     *
     * @return Customer|null
     */
    public function getAccountCustomer(Account $account, array $customersFields)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.account = :account');
        foreach ($customersFields as $customersField) {
            $qb->andWhere($qb->expr()->isNull(QueryBuilderUtil::getField('c', $customersField)));
        }

        return $qb->setParameter('account', $account)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
