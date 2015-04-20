<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Provider\CartConnector;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

class ContextCartReader extends CartConnector
{
    const CONTEXT_POST_PROCESS_CARTS = 'postProcessCartsIds';

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        // no need to load carts
        if (!$this->transport->isSupportedExtensionVersion()) {
            return new \EmptyIterator();
        }

        $iterator = parent::getConnectorSource();

        if (!$iterator instanceof UpdatedLoaderInterface) {
            return $iterator;
        }

        $iterator->setEntitiesIdsBuffer($this->getCartIds());

        return $iterator;
    }

    /**
     * @return array
     */
    public function getCartIds()
    {
        $ids = (array)$this->stepExecution->getJobExecution()
            ->getExecutionContext()->get(self::CONTEXT_POST_PROCESS_CARTS);

        sort($ids);

        return array_unique(array_filter($ids));
    }
}
