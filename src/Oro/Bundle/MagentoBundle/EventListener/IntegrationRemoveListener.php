<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Service\WsdlManager;

/**
 * Remove WSDL cache for integration scheduled for removal.
 */
class IntegrationRemoveListener
{
    /**
     * @var WsdlManager
     */
    protected $wsdlManager;

    /**
     * @param WsdlManager $wsdlManager
     */
    public function __construct(WsdlManager $wsdlManager)
    {
        $this->wsdlManager = $wsdlManager;
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof MagentoSoapTransport && $entity->getWsdlUrl()) {
            $this->wsdlManager->clearCacheForUrl($entity->getWsdlUrl());
        }
    }
}
