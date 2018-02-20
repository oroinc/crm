<?php

namespace Oro\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class ChannelAwareEntityRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    protected function applyActiveChannelLimitation(QueryBuilder $queryBuilder)
    {
        $rootAliases = $queryBuilder->getRootAliases();
        $alias = reset($rootAliases);

        $queryBuilder
            ->join($alias . '.dataChannel', 'channel')
            ->andWhere($queryBuilder->expr()->eq('channel.status', ':status'))
            ->setParameter('status', Channel::STATUS_ACTIVE);
    }
}
