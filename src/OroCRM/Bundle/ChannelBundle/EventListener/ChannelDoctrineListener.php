<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelDoctrineListener
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $settings         = $this->settingsProvider->getChannelTypeLifetimeValue();
        $em               = $args->getEntityManager();
        $uow              = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {

        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {

        }


    }
}
