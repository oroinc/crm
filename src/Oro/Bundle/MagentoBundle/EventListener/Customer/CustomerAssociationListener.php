<?php

namespace Oro\Bundle\MagentoBundle\EventListener\Customer;

use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Oro\Bundle\MagentoBundle\Customer\AssociationChecker;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;

/**
 * @deprecated since 2.0. This class will not be used.
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
