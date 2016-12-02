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
     * Returns Customer which have not assigned "target customers" by Account
     *
     * @param Account $account
     * @param array   $customersFields
     *
     * @return Customer|null
     */
    public function getCustomerWithoutAssociatedTargets(Account $account, array $customersFields)
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

    /**
     * Returns Customers with accounts which differs from provided accounts of target customers
     * could be used for sync purposes.
     *
     * @param array $customerTargets
     *  ['targetType1FQCN' => [
     *      ['target_id' => targetType1_id1, 'account_id' => targetType1_accountId1],
     *      ...
     *  ],
     *  ...
     * ]
     *
     * @return Customer[]
     */
    public function getSalesCustomersWithChangedAccount(array $customerTargets)
    {
        if (!$customerTargets) {
            return [];
        }
        $qb = $this->createQueryBuilder('sc');
        foreach ($customerTargets as $customerClass => $customers) {
            if (!$customers) {
                continue;
            }
            $customerField = AccountCustomerManager::getCustomerTargetField($customerClass);
            foreach ($customers as $customer) {
                $exprs = [];
                $customerParam = QueryUtils::generateParameterName('targetCustomer');
                $exprs[] = $qb->expr()->eq(
                    sprintf('sc.%s', $customerField),
                    sprintf(':%s', $customerParam)
                );
                $qb->setParameter($customerParam, $customer['target_id']);

                $exprs[] = $this->createChangedAccountExpr($qb, 'sc', $customer['account_id']);

                $qb->orWhere(call_user_func_array([$qb->expr(), 'andX'], $exprs));
            }
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param int          $accountId
     *
     * @return Expr
     */
    protected function createChangedAccountExpr(QueryBuilder $qb, $alias, $accountId = null)
    {
        if (null === $accountId) {
            return $qb->expr()->isNotNull(sprintf('%s.account', $alias));
        }

        $accountParam = QueryUtils::generateParameterName('account');
        $qb->setParameter($accountParam, $accountId);

        return $qb->expr()->neq(sprintf('%s.account', $alias), sprintf(':%s', $accountParam));
    }
}
