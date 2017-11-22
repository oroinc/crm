<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\MagentoBundle\Entity\Customer;

class CustomerCurrencyListener
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Customer           $entity
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Customer $entity, LifecycleEventArgs $event)
    {
        if (!$entity->getCurrency()) {
            $entity->setCurrency($this->getLocaleSettings()->getCurrency());
        }
    }

    /**
     * @return LocaleSettings
     */
    private function getLocaleSettings()
    {
        return $this->container->get('oro_locale.settings');
    }
}
