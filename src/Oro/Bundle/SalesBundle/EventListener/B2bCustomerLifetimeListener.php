<?php

namespace Oro\Bundle\SalesBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\Repository\B2bCustomerRepository;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Calculates and keeps actual B2B customer lifetime value.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class B2bCustomerLifetimeListener implements ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private ?CurrencyQueryBuilderTransformerInterface $qbTransformer = null;
    /** @var B2bCustomer[] */
    private array $queued = [];
    private bool $isInProgress = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_currency.query.currency_transformer' => CurrencyQueryBuilderTransformerInterface::class
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        $entities = $this->getChangedOpportunityEntities($uow);
        /** @var Opportunity $entity */
        foreach ($entities as $entity) {
            $b2bCustomer = null;
            if (!$entity->getId() && $this->isValuable($entity)) {
                // handle creation
                $b2bCustomer = $entity->getCustomerAssociation()->getTarget();
            } elseif ($uow->isScheduledForDelete($entity) && $this->isValuable($entity)) {
                $b2bCustomer = $entity->getCustomerAssociation()->getTarget();
            } elseif ($uow->isScheduledForUpdate($entity)) {
                // handle update
                $changeSet = $uow->getEntityChangeSet($entity);
                if ($this->isChangeSetValuable($changeSet)) {
                    $takeZeroRevenue = isset($changeSet['closeRevenueValue']);
                    if ($this->hasChangedB2bCustomer($changeSet)
                        && $this->isOldStatusValuable($entity, $changeSet)) {
                        // handle change of b2b customer
                        /** @var Customer $customer */
                        $customer = $changeSet['customerAssociation'][0];
                        $oldCustomer = $customer->getTarget();
                        /** @var B2bCustomer $oldCustomer */
                        $this->scheduleUpdate($oldCustomer, $uow);
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
                $this->scheduleUpdate($b2bCustomer, $uow);
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->isInProgress || empty($this->queued)) {
            return;
        }

        $em = $args->getEntityManager();
        $repo = $em->getRepository(B2bCustomer::class);

        $flushRequired = false;
        foreach ($this->queued as $b2bCustomer) {
            if (!$b2bCustomer->getId()) {
                // skip update for just removed customers
                continue;
            }

            $newLifetimeValue = $repo->calculateLifetimeValue($b2bCustomer, $this->getQbTransformer());
            /** @noinspection TypeUnsafeComparisonInspection */
            if ($newLifetimeValue != $b2bCustomer->getLifetime()) {
                $b2bCustomer->setLifetime($newLifetimeValue);
                $flushRequired = true;
            }
        }

        if ($flushRequired) {
            $this->isInProgress = true;
            try {
                $em->flush($this->queued);
            } finally {
                $this->isInProgress = false;
            }
        }

        $this->queued = [];
    }

    private function getChangedOpportunityEntities(UnitOfWork $uow): \Generator
    {
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (Opportunity::class === ClassUtils::getClass($entity)) {
                yield $entity;
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (Opportunity::class === ClassUtils::getClass($entity)) {
                yield $entity;
            }
        }
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if (Opportunity::class === ClassUtils::getClass($entity)) {
                yield $entity;
            }
        }
    }

    private function scheduleUpdate(B2bCustomer $b2bCustomer, UnitOfWork $uow): void
    {
        if ($uow->isScheduledForDelete($b2bCustomer)) {
            return;
        }

        $this->queued[$b2bCustomer->getId()] = $b2bCustomer;
    }

    private function isValuable(Opportunity $opportunity, bool $takeZeroRevenue = false): bool
    {
        return
            $this->hasB2bCustomerTarget($opportunity)
            && $opportunity->getStatus()
            && $opportunity->getStatus()->getId() === B2bCustomerRepository::VALUABLE_STATUS
            && ($takeZeroRevenue || $opportunity->getCloseRevenueValue() > 0);
    }

    private function isChangeSetValuable(array $changeSet): bool
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
            return \in_array(B2bCustomerRepository::VALUABLE_STATUS, $statusChangeSet, true);
        }

        return (bool)$fieldsUpdated;
    }

    private function getOldStatus(Opportunity $opportunity, array $changeSet): ?string
    {
        if (isset($changeSet['status'])
            && ClassUtils::getClass($changeSet['status'][0]) === ExtendHelper::buildEnumValueClassName(
                Opportunity::INTERNAL_STATUS_CODE
            )
        ) {
            return $changeSet['status'][0]->getId();
        }

        return $opportunity->getStatus()
            ? $opportunity->getStatus()->getId()
            : false;
    }

    private function hasB2bCustomerTarget(Opportunity $entity): bool
    {
        return
            $entity->getCustomerAssociation()
            && $entity->getCustomerAssociation()->getTarget() instanceof B2bCustomer;
    }

    private function hasChangedB2bCustomer(array $changeSet): bool
    {
        if (empty($changeSet['customerAssociation']) || !$changeSet['customerAssociation'][0]) {
            return false;
        }

        /** @var Customer $customer */
        $customer = $changeSet['customerAssociation'][0];

        return $customer->getTarget() instanceof B2bCustomer;
    }

    private function isOldStatusValuable(Opportunity $entity, array $changeSet): bool
    {
        return B2bCustomerRepository::VALUABLE_STATUS === $this->getOldStatus($entity, $changeSet);
    }

    private function getQbTransformer(): CurrencyQueryBuilderTransformerInterface
    {
        if (null === $this->qbTransformer) {
            $this->qbTransformer = $this->container->get('oro_currency.query.currency_transformer');
        }

        return $this->qbTransformer;
    }
}
