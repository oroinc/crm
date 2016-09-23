<?php

namespace Oro\Bundle\SalesBundle\Entity\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityListener
{
    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $em         = $event->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();
        $entities   = array_merge(
            $unitOfWork->getScheduledEntityInsertions(),
            $unitOfWork->getScheduledEntityUpdates()
        );
        foreach ($entities as $opportunity) {
            if ($opportunity instanceof Opportunity) {
                $entityChangeSet = $unitOfWork->getEntityChangeSet($opportunity);
                if (empty($entityChangeSet['status'])) {
                    return;
                }
                /** @var AbstractEnumValue $oldStatus */
                $oldStatus       = $entityChangeSet['status'][0];
                $oldStatusId     = $oldStatus ? $oldStatus->getId() : null;
                $newStatusId     = $opportunity->getStatus() ? $opportunity->getStatus()->getId() : null;
                $closedStatuses  = [Opportunity::STATUS_LOST, Opportunity::STATUS_WON];
                $valuableChanges = array_intersect([$oldStatusId, $newStatusId], $closedStatuses);
                if (in_array($newStatusId, $valuableChanges, true)) {
                    $opportunity->setClosedAt(new \DateTime('now', new \DateTimeZone('UTC')));
                    $unitOfWork->recomputeSingleEntityChangeSet(
                        $em->getClassMetadata(Opportunity::class),
                        $opportunity
                    );
                } elseif (in_array($oldStatusId, $valuableChanges, true)) {
                    $opportunity->setClosedAt(null);
                    $unitOfWork->recomputeSingleEntityChangeSet(
                        $em->getClassMetadata(Opportunity::class),
                        $opportunity
                    );
                }
            }
        }
    }
}
