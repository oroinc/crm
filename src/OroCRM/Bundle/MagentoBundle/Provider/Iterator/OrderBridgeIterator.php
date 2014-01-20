<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

class OrderBridgeIterator extends AbstractBridgeIterator
{
    /**
     * Get entities ids list
     *
     * @return mixed
     */
    protected function getEntityIds()
    {
        // TODO: Implement getEntityIds() method.
    }

    /**
     * Get entity data by id
     *
     * @param mixed $id
     *
     * @return mixed
     */
    protected function getEntity($id)
    {
        // TODO: Implement getEntity() method.
    }

    /**
     * Should return id field name for entity, e.g.: entity_id, order_id, increment_id, etc
     * Needed for complex filters for API calls
     *
     * @return string
     */
    protected function getIdFieldName()
    {
        // TODO: Implement getIdFieldName() method.
    }
}
