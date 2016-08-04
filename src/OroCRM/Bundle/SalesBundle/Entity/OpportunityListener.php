<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

class OpportunityListener
{
    /**
     * @param Opportunity       $opportunity
     * @param PreFlushEventArgs $event
     */
    public function preFlush(Opportunity $opportunity, PreFlushEventArgs $event)
    {
        $em           = $event->getEntityManager();
        $unitOfWork   = $em->getUnitOfWork();
        $originalData = $unitOfWork->getOriginalEntityData($opportunity);
        $newStatusId  = $opportunity->getStatus()->getId();
        if ($originalData) {
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(Opportunity::class), $opportunity);
            $entityChangeSet = $unitOfWork->getEntityChangeSet($opportunity);

            if (empty($entityChangeSet['status'])) {
                return;
            }

            /** @var AbstractEnumValue $oldStatus */
            $oldStatus   = $entityChangeSet['status'][0];
            $oldStatusId = $oldStatus->getId();

        } else {
            $oldStatusId = null;
        }
        $closedStatuses  = [OpportunityStatus::STATUS_LOST, OpportunityStatus::STATUS_WON];
        $valuableChanges = array_intersect([$oldStatusId, $newStatusId], $closedStatuses);
        if (in_array($newStatusId, $valuableChanges)) {
            $opportunity->setClosedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(Opportunity::class), $opportunity);
        } elseif (in_array($oldStatusId, $valuableChanges)) {
            $opportunity->setClosedAt(null);
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(Opportunity::class), $opportunity);
        }
    }
}
