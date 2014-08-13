<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelSaveSucceedListener
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var EntityManager */
    protected $em;

    /**
     * @param SettingsProvider $settingsProvider
     * @param EntityManager    $em
     */
    public function __construct(SettingsProvider $settingsProvider, EntityManager $em)
    {
        $this->settingsProvider = $settingsProvider;
        $this->em               = $em;
    }

    /**
     * @param ChannelSaveEvent $event
     */
    public function onChannelSucceedSave(ChannelSaveEvent $event)
    {
        /** @var Channel $channel */
        $channel    = $event->getChannel();
        $dataSource = $channel->getDataSource();

        if ($dataSource instanceof Integration) {
            $entities   = $channel->getEntities();
            $connectors = $this->getConnectors($entities);

            $dataSource->setConnectors($connectors);

            $this->em->persist($dataSource);
            $this->em->flush();
        }
    }

    /**
     * @param array $entities
     *
     * @return array
     */
    protected function getConnectors(array $entities)
    {
        $result = [];

        foreach ($entities as $entity) {
            array_push(
                $result,
                $this->settingsProvider->getIntegrationConnectorName($entity)
            );
        }

        return $result;
    }
}
