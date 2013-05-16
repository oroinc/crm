<?php

namespace Oro\Bundle\DataAuditBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\DataAuditBundle\Entity\Log;

class LogListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Unable to pass security context service here due to circular reference ((
     *
     * @param ContainerInterface $container DIC
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->setUser($eventArgs);
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $this->setUser($eventArgs);
    }

    protected function setUser(LifecycleEventArgs $eventArgs)
    {
        $security = $this->container->get('security.context');

        if ($eventArgs->getEntity() instanceof Log) {
            if ($security->getToken() && is_object($user = $security->getToken()->getUser())) {
                $eventArgs->getEntity()->setUser($user);
            } else {
                //throw new \RuntimeException('Unauthorised entity operation');
            }
        }
    }
}
