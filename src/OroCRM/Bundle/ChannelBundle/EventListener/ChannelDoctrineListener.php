<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
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
        $settings = $this->settingsProvider->getChannelTypeLifetimeValue();
        $em       = $args->getEntityManager();
        $uow      = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $className = ClassUtils::getClass($entity);
            if (in_array($className, $settings)) {
                $this->onInsert($entity);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $className = ClassUtils::getClass($entity);
            if (in_array($className, $settings)) {
                $this->onUpdate($entity);
            }
        }


    }

    protected function onInsert($entity)
    {

    }

    protected function onUpdate($entity)
    {

    }
}
