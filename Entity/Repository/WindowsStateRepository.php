<?php

namespace Oro\Bundle\WindowsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * WindowsState Repository
 */
class WindowsStateRepository extends EntityRepository
{
    /**
     * Find all Windows with states for specified user
     *
     * @param int $userId
     *
     * @return array
     */
    public function getWindowsStates($userId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->add(
            'select',
            new Expr\Select(
                array(
                    'ws.id',
                    'ws.data'
                )
            )
        )
        ->add('from', new Expr\From('Oro\Bundle\WindowsBundle\Entity\WindowsState', 'ws'))
        ->innerJoin('ws.user', 'u', Expr\Join::WITH)
        ->add(
            'where',
            $qb->expr()->andx(
                $qb->expr()->eq('u.id', ':userId')
            )
        )
//        ->add('orderBy', new Expr\OrderBy('ws.position', 'ASC'))
        ->setParameters(array('userId' => $userId));

        return $qb->getQuery()->getArrayResult();
    }
}
