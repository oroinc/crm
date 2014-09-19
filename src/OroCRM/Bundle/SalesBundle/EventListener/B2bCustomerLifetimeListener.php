<?php

namespace OroCRM\Bundle\SalesBundle\EventListener;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class B2bCustomerLifetimeListener
{
    /** @var UnitOfWork */
    protected $uow;

    /** @var EntityManager */
    protected $em;

    /** @var array */
    protected $queued = [];

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->initializeFromEventArgs($args);

        $entities = array_merge(
            $this->uow->getScheduledEntityInsertions(),
            $this->uow->getScheduledEntityDeletions(),
            $this->uow->getScheduledEntityUpdates()
        );

        /** @var Opportunity[] $entities */
        $entities = array_filter(
            $entities,
            function ($entity) {
                return 'OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity' === ClassUtils::getClass($entity);
            }
        );

        foreach ($entities as $entity) {
            if (!$entity->getId() && $this->isValuable($entity, true)) {
                // handle creation, just add to prev lifetime value and recalculate change set
                $b2bCustomer = $entity->getCustomer();
                $b2bCustomer->setLifetime($b2bCustomer->getLifetime() + $entity->getCloseRevenue());
                $this->uow->computeChangeSet(
                    $this->em->getClassMetadata(ClassUtils::getClass($b2bCustomer)),
                    $b2bCustomer
                );
            } elseif ($this->uow->isScheduledForDelete($entity) && $this->isValuable($entity)) {
                $this->scheduleUpdate($entity->getCustomer());
            } else {
                // handle update
                $changeSet = $this->uow->getEntityChangeSet($entity);
                
            }
        }
    }


    protected function scheduleUpdate(B2bCustomer $b2bCustomer)
    {

    }

    protected function isValuable(Opportunity $opportunity, $takeZeroRevenue = false)
    {
        return
            $opportunity->getCustomer() &&
            $opportunity->getStatus() &&
            $opportunity->getStatus()->getName() === 'won'  &&
            ($takeZeroRevenue || $opportunity->getCloseRevenue() > 0);
    }

    /**
     * @param PostFlushEventArgs|OnFlushEventArgs $args
     */
    protected function initializeFromEventArgs($args)
    {
        $this->em = $args->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();
    }
}
