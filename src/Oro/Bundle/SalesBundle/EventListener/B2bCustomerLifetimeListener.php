<?php

namespace Oro\Bundle\SalesBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CurrencyBundle\Converter\RateConverterInterface;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;
use Oro\Component\DependencyInjection\ServiceLink;

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

    /** @var RateConverterInterface */
    protected $rateConverter;

    /** @var CurrencyQueryBuilderTransformerInterface */
    protected $qbTransformer;

    public function __construct(
        ServiceLink $rateConverterLink,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->rateConverter = $rateConverterLink->getService();
        $this->qbTransformer = $qbTransformer;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                return 'Oro\\Bundle\\SalesBundle\\Entity\\Opportunity' === ClassUtils::getClass($entity);
            }
        );

        foreach ($entities as $entity) {
            $b2bCustomer = null;
            if (!$entity->getId() && $this->isValuable($entity)) {
                // handle creation
                $b2bCustomer       = $entity->getCustomerAssociation()->getTarget();
            } elseif ($this->uow->isScheduledForDelete($entity) && $this->isValuable($entity)) {
                $b2bCustomer       = $entity->getCustomerAssociation()->getTarget();
            } elseif ($this->uow->isScheduledForUpdate($entity)) {
                // handle update
                $changeSet = $this->uow->getEntityChangeSet($entity);
                if ($this->isChangeSetValuable($changeSet)) {
                    $takeZeroRevenue = isset($changeSet['closeRevenueValue']);
                    if ($this->hasChangedB2bCustomer($changeSet)
                        && $this->isOldStatusValuable($entity, $changeSet)) {
                        // handle change of b2b customer
                        /** @var Customer $customer */
                        $customer = $changeSet['customerAssociation'][0];
                        $oldCustomer = $customer->getTarget();
                        /** @var B2bCustomer $oldCustomer */
                        $this->scheduleUpdate($oldCustomer);
                    }
                    if ($this->isValuable($entity, $takeZeroRevenue)
                        || ($this->isOldStatusValuable($entity, $changeSet) && $this->hasB2bCustomerTarget($entity))
                    ) {
                        $b2bCustomer = $entity->getCustomerAssociation()->getTarget();
                    }
                }
            }
            if (null !== $b2bCustomer) {
                /** @var B2bCustomer $b2bCustomer */
                $this->scheduleUpdate($b2bCustomer);
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->isInProgress || empty($this->queued)) {
            return;
        }

        $this->initializeFromEventArgs($args);
        $repo = $this->em->getRepository('OroSalesBundle:B2bCustomer');

        $flushRequired = false;
        foreach ($this->queued as $b2bCustomer) {
            if (!$b2bCustomer->getId()) {
                // skip update for just removed customers
                continue;
            }

            $newLifetimeValue = $repo->calculateLifetimeValue($b2bCustomer, $this->qbTransformer);
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
            $this->hasB2bCustomerTarget($opportunity)
            && $opportunity->getStatus()
            && $opportunity->getStatus()->getId() === B2bCustomerRepository::VALUABLE_STATUS
            && ($takeZeroRevenue || $opportunity->getCloseRevenueValue() > 0);
    }

    /**
     * @param array $changeSet
     *
     * @return bool
     */
    protected function isChangeSetValuable(array $changeSet)
    {
        $fieldsUpdated = array_intersect(
            ['customerAssociation', 'status', 'closeRevenueValue', 'closeRevenueCurrency', 'baseCloseRevenueValue'],
            array_keys($changeSet)
        );

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

    /**
     * @param Opportunity $entity
     *
     * @return bool
     */
    protected function hasB2bCustomerTarget(Opportunity $entity)
    {
        return $entity->getCustomerAssociation()
               && $entity->getCustomerAssociation()->getTarget() instanceof B2bCustomer;
    }

    /**
     * @param array $changeSet
     *
     * @return bool
     */
    protected function hasChangedB2bCustomer(array $changeSet)
    {
        if (empty($changeSet['customerAssociation']) || !$changeSet['customerAssociation'][0]) {
            return false;
        }
        /** @var Customer $customer */
        $customer = $changeSet['customerAssociation'][0];

        return $customer->getTarget() instanceof B2bCustomer;
    }

    /**
     * @param Opportunity $entity
     * @param array       $changeSet
     *
     * @return bool
     */
    protected function isOldStatusValuable(Opportunity $entity, array $changeSet)
    {
        return B2bCustomerRepository::VALUABLE_STATUS === $this->getOldStatus($entity, $changeSet);
    }
}
