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
    const DEFAULT_MAX_RESULTS = 20;

    private $_config = array();

    /**
     * Setter for config
     *
     * @param $config
     * @return $this
     */
    public function setConfig($config) {
        $this->_config = $config;

        return $this;
    }

    /**
     * Find all Favorite items for specified user
     *
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     * @param string $type
     *
     * @return array
     */
    public function getNavigationItems($user, $type = null)
    {
        $maxResults = isset($this->_config['templates'][NavigationHistoryItem::NAVIGATION_HISTORY_ITEM_TYPE]['maxResults'])
                        ? $this->_config['templates'][NavigationHistoryItem::NAVIGATION_HISTORY_ITEM_TYPE]['maxResults']
                        : self::DEFAULT_MAX_RESULTS;
        $maxResults++;

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
                $qb->expr()->andx(
                    $qb->expr()->eq('ni.user', ':user')
                )
            )
            ->add('orderBy', new Expr\OrderBy('ni.updatedAt', 'DESC'))
            ->setMaxResults($maxResults)
            ->setParameters(array('user' => $user));

        return $qb->getQuery()->getArrayResult();
    }
}
