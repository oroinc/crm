<?php

namespace OroCRM\Bundle\SalesBundle\Entity\EntityListener;

use Doctrine\ORM\Event\PreFlushEventArgs;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus;

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
        $needRecompute = false;
        if ($originalData) {
            $needRecompute = true;
            $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(Opportunity::class), $opportunity);
            $entityChangeSet = $unitOfWork->getEntityChangeSet($opportunity);

            if (empty($entityChangeSet['status'])) {
                return;
            }

            /** @var AbstractEnumValue $oldStatus */
            $oldStatus   = $entityChangeSet['status'][0];
            $oldStatusId = $oldStatus ? $oldStatus->getId() : null;

        } else {
            $oldStatusId = null;
        }
        $closedStatuses  = [OpportunityStatus::STATUS_LOST, OpportunityStatus::STATUS_WON];
        $valuableChanges = array_intersect([$oldStatusId, $newStatusId], $closedStatuses);
        if (in_array($newStatusId, $valuableChanges)) {
            $opportunity->setClosedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            if ($needRecompute) {
                $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(Opportunity::class), $opportunity);
            }
        } elseif (in_array($oldStatusId, $valuableChanges)) {
            $opportunity->setClosedAt(null);
            if ($needRecompute) {
                $unitOfWork->recomputeSingleEntityChangeSet($em->getClassMetadata(Opportunity::class), $opportunity);
            }
        }
    }
}
