<?php

namespace OroCRM\Bundle\SalesBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class DefaultProbabilityListener
{
    /** @var ConfigManager $configManager */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Opportunity && $entity->getProbability() === null) {
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
     * @param Opportunity $opportunity
     *
     * @return float|null
     */
    private function getDefaultProbability(Opportunity $opportunity)
    {
        if (!$opportunity->getStatus()) {
            return null;
        }

        $probabilities = $this->configManager->get(
            'oro_crm_sales.default_opportunity_probabilities'
        );
 
        $statusId = $opportunity->getStatus()->getId();
        $probabilities = $this->configManager->get(
            'oro_crm_sales.default_opportunity_probabilities'
        );
 
        if (isset($probabilities[$statusId])) {
            return $probabilities[$statusId];
        }
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
