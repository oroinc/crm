<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;

class StateProvider
{
    const CACHE_ID = 'orocrm_channel_state_data';

    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var Cache */
    protected $cache;

    /** @var EntityManager */
    protected $em;

    /** @var null|array */
    protected $enabledEntities;

    /**
     * @param SettingsProvider $settingsProvider
     * @param Cache            $cache
     * @param EntityManager    $em
     */
    public function __construct(SettingsProvider $settingsProvider, Cache $cache, EntityManager $em)
    {
        $this->settingsProvider = $settingsProvider;
        $this->cache            = $cache;
        $this->em               = $em;
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
     * Event listener subscribed  on 'orocrm_channel.channel.save_succeed' event.
     */
    public function processChannelChange()
    {
        $this->cache->delete(self::CACHE_ID);
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

            $settings = $this->settingsProvider->getSettings('entity_data');

            $qb = $this->em->createQueryBuilder();
            $qb->distinct(true);
            $qb->select('i.type')
                ->from('OroCRMChannelBundle:Channel', 'c')
                ->innerJoin('c.integrations', 'i');

            $assignedIntegrationTypes = $qb->getQuery()->getArrayResult();
            $assignedIntegrationTypes = array_map(
                function ($result) {
                    return $result['type'];
                },
                $assignedIntegrationTypes
            );

            $qb = $this->em->createQueryBuilder();
            $qb->distinct(true);
            $qb->select('e.value')
                ->from('OroCRMChannelBundle:Channel', 'c')
                ->innerJoin('c.entities', 'e');

            $assignedEntityNames = $qb->getQuery()->getArrayResult();
            $assignedEntityNames = array_map(
                function ($result) {
                    return $result['value'];
                },
                $assignedEntityNames
            );

            $this->enabledEntities = [];
            foreach ($settings as $entityName => $singleEntitySettings) {
                if (in_array($entityName, $assignedEntityNames, true)) {
                    $this->enabledEntities[$entityName] = true;
                } elseif ($this->settingsProvider->belongsToIntegration($entityName)) {
                    $type                               = $this->settingsProvider->getIntegrationTypeData($entityName);
                    $this->enabledEntities[$entityName] = in_array($type, $assignedIntegrationTypes, true);
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
        $fetchResult = $this->cache->fetch(self::CACHE_ID);

        return is_array($fetchResult) ? $fetchResult : false;
    }

    /**
     * Persist data to cache
     */
    protected function persistToCache()
    {
        $this->cache->save(self::CACHE_ID, $this->enabledEntities);
    }
}
