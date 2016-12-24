<?php

namespace Oro\Bridge\MarketingCRM\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

class LoadClassMetadataSubscriber implements EventSubscriber
{
    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();

        if ($classMetadata->getName() !== 'Oro\Bundle\ChannelBundle\Entity\Channel') {
            return;
        }

        $classMetadata->customRepositoryClassName = 'Oro\Bridge\MarketingCRM\Entity\Repository\ChannelRepository';
    }
}
