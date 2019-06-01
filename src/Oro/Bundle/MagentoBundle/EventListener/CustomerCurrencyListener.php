<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\MagentoBundle\Entity\Customer;

/**
 * Apply currency to magento customer entity
 */
class CustomerCurrencyListener
{
    /** @var LocaleSettings */
    private $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * @param Customer           $entity
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Customer $entity, LifecycleEventArgs $event)
    {
        if (!$entity->getCurrency()) {
            $entity->setCurrency($this->localeSettings->getCurrency());
        }
    }
}
