<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

class AddOrUpdateCustomer implements StrategyInterface
{
    /**
     * Process item strategy
     *
     * @param mixed $entity
     * @return mixed|null
     */
    public function process($entity)
    {
        $result = $this->findGroup($entity->getId());
        if (!$result) {
            $result = $this->createGroup();
        }
        $this->replaceGroupProperties($result, $entity);
        $this->validateGroup($result);
        return $result;
    }
}
