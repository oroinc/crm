<?php

namespace Oro\Bundle\ActivityContactBundle\Tools;

use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Determines and stores only changed activity targets identifiers
 */
class ActivityListenerChangedTargetsBag
{
    private DoctrineHelper $doctrineHelper;

    private array $identifiers = [];

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    public function add(array $targets, UnitOfWork $uow): void
    {
        foreach ($targets as $target) {
            $changeSet = $uow->getEntityChangeSet($target);

            if (!$changeSet) {
                continue;
            }

            if (array_key_exists(ActivityScope::CONTACT_COUNT, $changeSet)
                || array_key_exists(ActivityScope::CONTACT_COUNT_IN, $changeSet)) {
                $this->identifiers[] = $this->getTargetKey($target);
            }
        }
    }

    public function isChanged(object $target): bool
    {
        return \in_array($this->getTargetKey($target), $this->identifiers, true);
    }

    public function clear(): void
    {
        $this->identifiers = [];
    }

    private function getTargetKey(object $target): string
    {
        $class = $this->doctrineHelper->getEntityClass($target);
        $id = $this->doctrineHelper->getSingleEntityIdentifier($target);
        return $class . '_' . $id;
    }
}
