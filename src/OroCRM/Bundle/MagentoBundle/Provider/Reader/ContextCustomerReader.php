<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnector;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

class ContextCustomerReader extends CustomerConnector
{
    /** @var string */
    protected $contextKey;

    /**
     * @param string $contextKey
     */
    public function setContextKey($contextKey)
    {
        $this->contextKey = $contextKey;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        $iterator = parent::getConnectorSource();

        if (!$iterator instanceof UpdatedLoaderInterface) {
            return $iterator;
        }

        $iterator->setEntitiesIdsBuffer($this->getCustomerIds());

        return $iterator;
    }

    /**
     * @return array
     */
    public function getCustomerIds()
    {
        if (!$this->contextKey) {
            throw new \InvalidArgumentException('Context key is missing');
        }

        $entities = (array)$this->stepExecution->getJobExecution()->getExecutionContext()->get($this->contextKey);

        $entitiesIdsBuffer = array_map(
            function (array $entity) {
                if (empty($entity['customer']['originId'])) {
                    return false;
                }

                return $entity['customer']['originId'];
            },
            $entities
        );

        return array_unique(array_filter($entitiesIdsBuffer));
    }
}
