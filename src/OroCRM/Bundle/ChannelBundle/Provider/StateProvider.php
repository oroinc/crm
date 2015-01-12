<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\Cache;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class StateProvider
{
    const CACHE_ID = 'orocrm_channel_state_data';

    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var Cache */
    protected $cache;

    /** @var RegistryInterface */
    protected $registry;

    /** @var null|array */
    protected $enabledEntities;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var ServiceLink */
    protected $securityFacadeLink;

    /**
     * @param SettingsProvider  $settingsProvider
     * @param Cache             $cache
     * @param RegistryInterface $registry
     * @param ServiceLink       $securityFacadeLink
     * @param AclHelper         $aclHelper
     */
    public function __construct(
        SettingsProvider $settingsProvider,
        Cache $cache,
        RegistryInterface $registry,
        ServiceLink $securityFacadeLink,
        AclHelper $aclHelper
    ) {
        $this->settingsProvider = $settingsProvider;
        $this->cache = $cache;
        $this->registry = $registry;
        $this->securityFacadeLink = $securityFacadeLink;
        $this->aclHelper = $aclHelper;
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
        $this->ensureLocalCacheWarmed();

        return array_key_exists($entityFQCN, $this->enabledEntities) && $this->enabledEntities[$entityFQCN] === true;
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
        $qb->from('OroCRMChannelBundle:Channel', 'c');
        $qb->select('c.id');
        $qb->andWhere('c.status = :status');
        $qb->setParameter('status', Channel::STATUS_ACTIVE);

        if (!empty($entities)) {
            $countDistinctName = $qb->expr()->eq($qb->expr()->countDistinct('e.name'), ':count');

            $qb->innerJoin('c.entities', 'e');
            $qb->andWhere($qb->expr()->in('e.name', $entities));
            $qb->groupBy('c.name', 'c.id');
            $qb->having($countDistinctName);
            $qb->setParameter('count', count($entities));
        }

        return (bool)$this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * Event listener subscribed  on 'orocrm_channel.channel.save_succeed' and on
     * 'orocrm_channel.channel.delete_succeed' event.
     */
    public function processChannelChange()
    {
        $this->cache->delete($this->getCacheId());
    }

    /**
     * Clear state cache for given organization
     *
     * @param $organizationId
     */
    public function clearOrganizationCache($organizationId)
    {
        $this->cache->delete($this->getCacheId($organizationId));
    }

    /**
     * Warm up local data cache in order to prevent multiple queries to DB
     */
    protected function ensureLocalCacheWarmed()
    {
        if ($this->enabledEntities === null) {
            if (false !== ($data = $this->tryCacheLookUp())) {
                $this->enabledEntities = $data;

                return;
            }

            $settings = $this->settingsProvider->getSettings(SettingsProvider::DATA_PATH);

            $qb = $this->getManager()->createQueryBuilder();
            $qb->distinct(true);
            $qb->select('e.name')
                ->from('OroCRMChannelBundle:Channel', 'c')
                ->andWhere('c.status = :status')
                ->setParameter('status', Channel::STATUS_ACTIVE)
                ->innerJoin('c.entities', 'e');

            $assignedEntityNames = $this->aclHelper->apply($qb)->getArrayResult();
            $assignedEntityNames = array_map(
                function ($result) {
                    return $result['name'];
                },
                $assignedEntityNames
            );

            $this->enabledEntities = [];
            foreach (array_keys($settings) as $entityName) {
                if (in_array($entityName, $assignedEntityNames, true)) {
                    $this->enabledEntities[$entityName] = true;
                }
            }
            $this->persistToCache();
        }
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
    protected function persistToCache()
    {
        $this->cache->save($this->getCacheId(), $this->enabledEntities);
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
            $organizationId = $this->securityFacadeLink->getService()->getOrganizationId();
        }
        return self::CACHE_ID . '_' . $organizationId;
    }
}
