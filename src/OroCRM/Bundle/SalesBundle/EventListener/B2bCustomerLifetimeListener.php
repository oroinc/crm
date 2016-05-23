<?php

namespace OroCRM\Bundle\SalesBundle\EventListener;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;

class B2bCustomerLifetimeListener
{
    /** @var UnitOfWork */
    protected $uow;

    /** @var EntityManager */
    protected $em;

    /** @var B2bCustomer[] */
    protected $queued = [];

    /** @var bool */
    protected $isInProgress = false;

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
            if (!$entity->getId() && $this->isValuable($entity)) {
                // handle creation, just add to prev lifetime value and recalculate change set
                $b2bCustomer = $entity->getCustomer();
                $b2bCustomer->setLifetime($b2bCustomer->getLifetime() + $entity->getCloseRevenue());
                $this->scheduleUpdate($b2bCustomer);
                $this->uow->computeChangeSet(
                    $this->em->getClassMetadata(ClassUtils::getClass($b2bCustomer)),
                    $b2bCustomer
                );
            } elseif ($this->uow->isScheduledForDelete($entity) && $this->isValuable($entity)) {
                $this->scheduleUpdate($entity->getCustomer());
            } elseif ($this->uow->isScheduledForUpdate($entity)) {
                // handle update
                $changeSet = $this->uow->getEntityChangeSet($entity);

                if ($this->isChangeSetValuable($changeSet)) {
                    if (!empty($changeSet['customer'])
                        && $changeSet['customer'][0] instanceof B2bCustomer
                        && B2bCustomerRepository::VALUABLE_STATUS === $this->getOldStatus($entity, $changeSet)
                    ) {
                        // handle change of b2b customer
                        $this->scheduleUpdate($changeSet['customer'][0]);
                    }

                    if ($this->isValuable($entity, isset($changeSet['closeRevenue']))
                        || (
                            B2bCustomerRepository::VALUABLE_STATUS === $this->getOldStatus($entity, $changeSet)
                            && $entity->getCustomer()
                        )
                    ) {
                        $this->scheduleUpdate($entity->getCustomer());
                    }
                }
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->isInProgress || empty($this->queued)) {
            return;
        }

        $this->initializeFromEventArgs($args);
        $repo = $this->em->getRepository('OroCRMSalesBundle:B2bCustomer');

        $flushRequired = false;
        foreach ($this->queued as $b2bCustomer) {
            if (!$b2bCustomer->getId()) {
                // skip update for just removed customers
                continue;
            }

            $newLifetimeValue = $repo->calculateLifetimeValue($b2bCustomer);
            if ($newLifetimeValue != $b2bCustomer->getLifetime()) {
                $b2bCustomer->setLifetime($newLifetimeValue);
                $flushRequired = true;
            }
        }

        if ($flushRequired) {
            $this->isInProgress = true;

            $this->em->flush($this->queued);

            $this->isInProgress = false;
        }

        $this->queued = [];
    }

    /**
     * @param B2bCustomer $b2bCustomer
     */
    protected function scheduleUpdate(B2bCustomer $b2bCustomer)
    {
        if ($this->uow->isScheduledForDelete($b2bCustomer)) {
            return;
        }

        $this->queued[$b2bCustomer->getId()] = $b2bCustomer;
    }

    /**
     * @param Opportunity $opportunity
     * @param bool        $takeZeroRevenue
     *
     * @return bool
     */
    protected function isValuable(Opportunity $opportunity, $takeZeroRevenue = false)
    {
        return
            $opportunity->getCustomer()
            && $opportunity->getStatus()
            && $opportunity->getStatus()->getId() === B2bCustomerRepository::VALUABLE_STATUS
            && ($takeZeroRevenue || $opportunity->getCloseRevenue() > 0);
    }

    /**
     * @param array $changeSet
     *
     * @return bool
     */
    protected function isChangeSetValuable(array $changeSet)
    {
        $fieldsUpdated = array_intersect(['customer', 'status', 'closeRevenue'], array_keys($changeSet));

        if (!empty($changeSet['status'])) {
            $statusChangeSet = array_map(
                function ($status = null) {
                    return $status ? $status->getId() : null;
                },
                $changeSet['status']
            );

            // if status was changed, check whether it had/has needed value
            return in_array(B2bCustomerRepository::VALUABLE_STATUS, $statusChangeSet, true);
        }

        return (bool)$fieldsUpdated;
    }

    /**
     * @param Opportunity $opportunity
     * @param array       $changeSet
     *
     * @return bool|string
     */
    protected function getOldStatus(Opportunity $opportunity, array $changeSet)
    {
        $enumClass = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        return isset($changeSet['status']) && ClassUtils::getClass($changeSet['status'][0]) === $enumClass
            ? $changeSet['status'][0]->getId()
            : ($opportunity->getStatus() ? $opportunity->getStatus()->getId() : false);
    }

    /**
     * @param PostFlushEventArgs|OnFlushEventArgs $args
     */
    protected function initializeFromEventArgs($args)
    {
        $this->em  = $args->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();
    }
}
