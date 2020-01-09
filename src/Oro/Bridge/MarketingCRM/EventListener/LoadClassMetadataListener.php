<?php

namespace Oro\Bridge\MarketingCRM\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

/**
 * Replace the repository for Channel entity.
 */
class LoadClassMetadataListener
{
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
