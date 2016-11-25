<?php

namespace Oro\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Customer;

class CustomerRepository extends EntityRepository
{
    /**
     * Returns Customer which have not assigned "target customers" by Account
     *
     * @param Account $account
     * @param array   $customersFields
     *
     * @return Customer
     */
    public function getAccountCustomer(Account $account, array $customersFields)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.account =: account');
        foreach ($customersFields as $customersField) {
            $qb->andWhere($qb->expr()->isNull(sprintf('c.%s', $customersField)));
        }

        return $qb->setParameters(['account', $account])
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param int $targetId
     * @param string $targetField
     *
     * @return Customer|null
     */
    public function getCustomerByTargetCustomer($targetId, $targetField)
    {
        return $this->findOneBy([$targetField => $targetId]);
    }
}
