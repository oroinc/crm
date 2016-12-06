<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Oro\Component\DoctrineUtils\ORM\QueryUtils;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

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
            ->where(sprintf('IDENTITY(c.%s) = :targetId', $targetField));

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
            $qb->andWhere($qb->expr()->isNull(sprintf('c.%s', $customersField)));
        }

        return $qb->setParameter('account', $account)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}
