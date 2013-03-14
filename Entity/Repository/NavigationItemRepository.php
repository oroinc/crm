<?php

namespace Oro\Bundle\NavigationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * NavigationItem Repository
 */
class NavigationItemRepository extends EntityRepository implements NavigationRepositoryInterface
{
    /**
     * Find all Favorite items for specified user
     *
     * @param int $userId
     * @param string $type
     *
     * @return array
     */
    public function getNavigationItems($userId, $type)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->add(
            'select',
            new Expr\Select(
                array(
                    'ni.id',
                    'ni.url',
                    'ni.title',
                    'ni.type'
                )
            )
        )
        ->add('from', new Expr\From('Oro\Bundle\NavigationBundle\Entity\NavigationItem', 'ni'))
        ->innerJoin('ni.user', 'u', Expr\Join::WITH)
        ->add(
            'where',
            $qb->expr()->andx(
                $qb->expr()->eq('u.id', ':userId'),
                $qb->expr()->eq('ni.type', ':type')
            )
        )
        ->add('orderBy', new Expr\OrderBy('ni.position', 'ASC'))
        ->setParameters(array('userId' => $userId, 'type' => $type));

        return $qb->getQuery()->getArrayResult();
    }
}
