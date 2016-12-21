<?php

namespace Oro\Bundle\MagentoBundle\EventListener\Customer;

use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Oro\Bundle\MagentoBundle\Customer\AssociationChecker;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;

/**
 * @TODO This listener should be removed after CRM-7178 will be fixed
 */
class CustomerAssociationListener
{
    /** @var AssociationChecker */
    protected $checker;

    /**
     * @param AssociationChecker $checker
     */
    public function __construct(AssociationChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * @param BeforeViewRenderEvent $event
     */
    public function checkCustomer(BeforeViewRenderEvent $event)
    {
        $entity = $event->getEntity();
        if (!$entity instanceof MagentoCustomer) {
            return;
        }

        $this->checker->fixAssociation($entity);
    }
}
