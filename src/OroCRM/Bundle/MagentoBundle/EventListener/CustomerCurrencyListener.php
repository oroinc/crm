<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerCurrencyListener implements ContainerAwareInterface
{
    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @var ContainerInterface|null
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        /** @var Customer $entity */
        $entity = $event->getEntity();
        $localeSettings = $this->getLocaleSettings();

        if ($localeSettings && $entity instanceof Customer && !$entity->getCurrency()) {
            $entity->setCurrency($localeSettings->getCurrency());
        }
    }

    protected function getLocaleSettings()
    {
        if (!$this->localeSettings) {
            $this->localeSettings = $this->container->get('oro_locale.settings');
        }

        return $this->localeSettings;
    }
}
