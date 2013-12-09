<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

class RegionStrategy extends BaseStrategy
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Region';

    /**
     * Process item strategy
     *
     * @param mixed $entity
     * @return mixed|null
     */
    public function process($entity)
    {
        $entity = $this->findAndReplaceEntity($entity, self::ENTITY_NAME, 'combinedCode');

        // validate and update context - increment counter or add validation error
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }
}
