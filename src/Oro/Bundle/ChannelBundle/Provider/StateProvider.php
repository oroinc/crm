<?php

namespace Oro\Bundle\ChannelBundle\Provider;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Provider that allows to check whether entity is enabled in current system state
 * and to check are there any channel with all listed entities enabled
 */
class StateProvider
{
    const CACHE_ID = 'oro_channel_state_data';

    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var Cache */
    protected $cache;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    public function __construct(
        SettingsProvider $settingsProvider,
        Cache $cache,
        ManagerRegistry $registry,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->settingsProvider = $settingsProvider;
        $this->cache = $cache;
        $this->registry = $registry;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * Checks whether entity is enabled in current system state
     *
     * @param string $entityFQCN entity full class name
     *
     * @return bool
     */
    public function isEntityEnabled($entityFQCN)
    {
        $enabledEntities = $this->getEnabledEntities();

        return
            array_key_exists($entityFQCN, $enabledEntities)
            && $enabledEntities[$entityFQCN] === true;
    }

    /**
     * Check are there any channel with all listed entities enabled
     *
     * @param array $entities
     *
     * @return bool
     */
    public function isEntitiesEnabledInSomeChannel(array $entities)
    {
        $qb = $this->getManager()->createQueryBuilder('c');
        $qb->from('OroChannelBundle:Channel', 'c');
        $qb->select('c.id');

        if (!empty($entities)) {
            $countDistinctName = $qb->expr()->eq($qb->expr()->countDistinct('e.name'), ':count');

            $qb->innerJoin('c.entities', 'e');
            $qb->andWhere($qb->expr()->in('e.name', ':entitiesNames'));
            $qb->setParameter('entitiesNames', $entities);
            $qb->groupBy('c.name', 'c.id');
            $qb->having($countDistinctName);
            $qb->setParameter('count', count($entities));
        }

        $organizationId = $this->tokenAccessor->getOrganizationId();
        if ($organizationId) {
            // at this query we should not use ACL helper and should just add limitation by organization.
            // If here ACL helper will be used, there is a case, when a user have no permission to view Channel
            // entity. In this case, empty result will be returned. But at this query we select entities that user
            // might have access (entities that used at the channel).
            $qb->andWhere('c.owner = :organizationId')
                ->setParameter('organizationId', $organizationId);
        }

        return (bool)$qb->getQuery()->getArrayResult();
    }

    /**
     * Delete cache for channel
     */
    public function processChannelChange()
    {
        $this->cache->delete($this->getCacheId());
    }

    /**
     * Clear state cache for given organization
     */
    public function clearOrganizationCache($organizationId)
    {
        $this->cache->delete($this->getCacheId($organizationId));
    }

    /**
     * Returns a list of enabled entities form the cache. In case if cache does not have any data - warms up
     * data into the cache and return them.
     */
    protected function getEnabledEntities()
    {
        $data = $this->tryCacheLookUp();
        if (false !== $data) {
            return $data;
        }

        $qb = $this->getManager()->createQueryBuilder();
        $qb->distinct(true);
        $qb->select('e.name')
            ->from('OroChannelBundle:Channel', 'c')
            ->innerJoin('c.entities', 'e');

        $organizationId = $this->tokenAccessor->getOrganizationId();
        if ($organizationId) {
            // at this query we should not use ACL helper and should just add limitation by organization.
            // If here ACL helper will be used, there is a case, when a user have no permission to view Channel
            // entity. In this case, empty result will be returned. But at this query we select entities that user
            // might have access (entities that used at the channel).
            $qb->andWhere('c.owner = :organizationId')
                ->setParameter('organizationId', $organizationId);
        }

        $assignedEntityNames = $qb->getQuery()->getArrayResult();
        $assignedEntityNames = array_map(
            function ($result) {
                return $result['name'];
            },
            $assignedEntityNames
        );

        $enabledEntities = [];

        $settings = $this->settingsProvider->getEntities();
        foreach (array_keys($settings) as $entityName) {
            if (in_array($entityName, $assignedEntityNames, true)) {
                $enabledEntities[$entityName] = true;
            }
        }
        $this->persistToCache($enabledEntities);

        return $enabledEntities;
    }

    /**
     * Try to fetch data from cache
     *
     * @return bool|array
     */
    protected function tryCacheLookUp()
    {
        $fetchResult = $this->cache->fetch($this->getCacheId());

        return is_array($fetchResult) ? $fetchResult : false;
    }

    /**
     * Persist data to cache
     */
    protected function persistToCache(array $enabledEntities)
    {
        $this->cache->save($this->getCacheId(), $enabledEntities);
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->registry->getManager();
    }

    /**
     * Get cache ID for given organization id. If id was not set, get cache ID depending on the current organization
     *
     * @param int $organizationId
     * @return string
     */
    protected function getCacheId($organizationId = null)
    {
        if (!$organizationId) {
            $organizationId = $this->tokenAccessor->getOrganizationId();
        }

        return self::CACHE_ID . '_' . $organizationId;
    }
}
