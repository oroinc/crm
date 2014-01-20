<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

class OrderBridgeIterator extends AbstractBridgeIterator
{
    /**
     * Get entities list
     *
     * @param array $filters
     * @param null  $limit
     * @param bool  $idsOnly
     *
     * @return mixed
     */
    protected function getList($filters = [], $limit = null, $idsOnly = true)
    {
        // TODO: Implement getList() method.
    }

    /**
     * Get entity data by id
     *
     * @param int        $id
     * @param bool       $dependenciesInclude
     * @param array|null $onlyAttributes array of needed attributes or null to get all list
     *
     * @return mixed
     */
    protected function getData($id, $dependenciesInclude = false, $onlyAttributes = null)
    {
        // TODO: Implement getData() method.
    }

    /**
     * Should return id field name for entity, e.g.: entity_id, order_id, increment_id, etc
     *
     * @return string
     */
    protected function getIdFieldName()
    {
        // TODO: Implement getIdFieldName() method.
    }

    /**
     * Do real load for dependencies data
     *
     * @return void
     */
    protected function loadDependencies()
    {
        // TODO: Implement loadDependencies() method.
    }


}
