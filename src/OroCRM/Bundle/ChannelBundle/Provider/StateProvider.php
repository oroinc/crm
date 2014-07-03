<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\EntityManager;

class StateProvider
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var EntityManager */
    protected $em;

    /** @var null|array */
    protected $enabledEntities;

    /**
     * @param EntityManager    $em
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(SettingsProvider $settingsProvider, EntityManager $em)
    {
        $this->settingsProvider = $settingsProvider;
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
     * Warm up local data cache in order to prevent multiple queries to DB
     */
    protected function ensureLocalCacheWarmed()
    {
        if ($this->enabledEntities === null) {
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
        }
    }
}
