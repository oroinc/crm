<?php

namespace Oro\Bundle\NavigationBundle\Entity\Repository;

use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * NavigationItem Repository
 */
class HistoryItemRepository extends EntityRepository implements NavigationRepositoryInterface
{
    /**
     * Find all Favorite items for specified user
     *
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     * @param string $type
     * @param array $options
     *
     * @return array
     */
    public function getNavigationItems($user, $type = null, $options = array())
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->add(
            'select',
            new Expr\Select(
                array(
                    'ni.id',
                    'ni.url',
                    'ni.title',
                )
            )
        )
            ->add('from', new Expr\From('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem', 'ni'))
            ->add(
                'where',
                $qb->expr()->eq('ni.user', ':user')
            )
            ->add('orderBy', new Expr\OrderBy('ni.visitedAt', 'DESC'))
            ->setMaxResults($options['maxItems'])
            ->setParameters(array('user' => $user));

        return $qb->getQuery()->getArrayResult();
    }
}
