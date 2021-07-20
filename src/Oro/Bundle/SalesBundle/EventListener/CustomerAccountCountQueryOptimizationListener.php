<?php

namespace Oro\Bundle\SalesBundle\EventListener;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\BatchBundle\Event\CountQueryOptimizationEvent;
use Oro\Bundle\BatchBundle\ORM\QueryBuilder\QueryOptimizationContext;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

/**
 * Removes a join to {@see \Oro\Bundle\SalesBundle\Entity\Customer} when it is used
 * to get an account and a join to the account was removed.
 */
class CustomerAccountCountQueryOptimizationListener
{
    public function onOptimize(CountQueryOptimizationEvent $event): void
    {
        $context = $event->getContext();
        $joinAliases = $event->getOptimizedQueryJoinAliases();
        foreach ($joinAliases as $alias) {
            if ($this->isCustomerAssociationLeftJoin($alias, $context, $joinAliases)) {
                $event->removeJoinFromOptimizedQuery($alias);
            }
        }
    }

    /**
     * @param string                   $alias
     * @param QueryOptimizationContext $context
     * @param string[]                 $requiredJoinAliases
     *
     * @return bool
     */
    private function isCustomerAssociationLeftJoin(
        string $alias,
        QueryOptimizationContext $context,
        array $requiredJoinAliases
    ): bool {
        $join = $context->getJoinByAlias($alias);
        if ($join->getJoinType() !== Expr\Join::LEFT_JOIN || !$join->getCondition()) {
            return false;
        }

        $className = $context->getEntityClassByAlias($alias);
        if (!is_a($className, Customer::class, true)) {
            return false;
        }

        $result = false;
        $condition = trim($join->getCondition());
        $qb = $context->getOriginalQueryBuilder();
        /** @var Expr\From[] $fromParts */
        $fromParts = $qb->getDQLPart('from');
        foreach ($fromParts as $from) {
            $rootAlias = $from->getAlias();
            $associationName = AccountCustomerManager::getCustomerTargetField($from->getFrom());
            if ($condition === "$alias.$associationName = $rootAlias"
                && $this->isOptionalAccountAssociation($qb, $rootAlias, $alias, $requiredJoinAliases)
            ) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $rootAlias
     * @param string       $customerAssociationAlias
     * @param string[]     $requiredJoinAliases
     *
     * @return bool
     */
    private function isOptionalAccountAssociation(
        QueryBuilder $qb,
        string $rootAlias,
        string $customerAssociationAlias,
        array $requiredJoinAliases
    ): bool {
        $accountJoinExpr = $customerAssociationAlias . '.account';
        $joinParts = $qb->getDQLPart('join');
        /** @var Expr\Join $join */
        foreach ($joinParts[$rootAlias] as $join) {
            if ($join->getJoin() === $accountJoinExpr && !\in_array($join->getAlias(), $requiredJoinAliases, true)) {
                return true;
            }
        }

        return false;
    }
}
