<?php

namespace OroCRM\Bundle\SalesBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WorkflowBundle\Restriction\RestrictionManager;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class DefaultProbabilityListener
{
    /** @var ConfigManager $configManager */
    protected $configManager;

    /** @var RestrictionManager $restrictionManager */
    protected $restrictionManager;

    /**
     * @param ConfigManager      $configManager
     * @param RestrictionManager $restrictionManager
     */
    public function __construct(ConfigManager $configManager, RestrictionManager $restrictionManager)
    {
        $this->configManager = $configManager;
        $this->restrictionManager = $restrictionManager;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Opportunity) {
            return;
        }

        if ($this->hasWorkflowRestriction($entity)) {
            return;
        }

        if ($entity->getProbability() === null) {
            $entity->setProbability($this->getDefaultProbability($entity));
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Opportunity) {
            return;
        }

        if ($this->hasWorkflowRestriction($entity)) {
            return;
        }

        $probability = $this->getDefaultProbability($entity);
        if (null === $probability) {
            return;
        }

        if (!$args->hasChangedField('status')) {
            return;
        }

        if ($args->hasChangedField('probability')) {
            if (null === $args->getNewValue('probability')) {
                $args->setNewValue('probability', $probability);
            }

            return;
        }

        $entity->setProbability($probability);
        $this->recomputeChangeSet($args);
    }

    /**
     * Checks if opportunity has an restriction of probability field
     *
     * @param  Opportunity $opportunity
     * @return bool
     */
    protected function hasWorkflowRestriction(Opportunity $opportunity)
    {
        if (!$this->restrictionManager->hasEntityClassRestrictions(Opportunity::class)) {
            return false;
        }

        $restrictions = (array) $this->restrictionManager->getEntityRestrictions($opportunity);

        return in_array('probability', array_column($restrictions, 'field'));
    }

    /**
     * @param Opportunity $opportunity
     *
     * @return float|null
     */
    protected function getDefaultProbability(Opportunity $opportunity)
    {
        if (!$opportunity->getStatus()) {
            return null;
        }

        $statusId = $opportunity->getStatus()->getId();
        $probabilities = $this->configManager->get(Opportunity::PROBABILITIES_CONFIG_KEY);

        if (isset($probabilities[$statusId])) {
            return $probabilities[$statusId];
        }

        return null;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function recomputeChangeSet(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $meta = $em->getClassMetadata(get_class($entity));
        $uow->recomputeSingleEntityChangeSet($meta, $entity);
    }
}
